<?php

namespace App\Http\Controllers\V2;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class IndicatorPerformanceController extends Controller
{
  /**
   * Constructor - no middleware here
   */
  public function __construct()
  {
      // Empty constructor - we'll use a private method for auth checks
  }

  /**
   * Check if the user is authorized to access ECSA-HC features
   *
   * @return \Illuminate\Http\RedirectResponse|null
   */
  private function checkEcsaHcAuthorization()
  {
      if (!Auth::check() || Auth::user()->UserType !== 'ECSA-HC') {
          return redirect('/');
      }

      return null; // User is authorized
  }

  /**
   * Display the cluster selection view
   */
  public function selectCluster()
  {
      // Check authorization
      if ($redirect = $this->checkEcsaHcAuthorization()) {
          return $redirect;
      }

      try {
          // Get user information
          $user = Auth::user();
          $clusters = [];

          // Admin users can see all clusters
          if ($user->AccountRole === 'Admin') {
              $clusters = DB::select("
                  SELECT ClusterID, Cluster_Name, Description
                  FROM clusters
                  ORDER BY Cluster_Name
              ");
          } else {
              // Non-admin users can only see their assigned cluster
              $clusters = DB::select("
                  SELECT ClusterID, Cluster_Name, Description
                  FROM clusters
                  WHERE ClusterID = ?
                  ORDER BY Cluster_Name
              ", [$user->ClusterID]);
          }

          // Verify data integrity
          if (empty($clusters)) {
              return view('scrn', [
                  'Page' => 'EcsaPerformanceReports.IndPerfSelectCluster',
                  'clusters' => [],
                  'error' => 'No clusters found. Please contact the administrator.',
                  'user' => $user
              ]);
          }

          return view('scrn', [
              'Page' => 'EcsaPerformanceReports.IndPerfSelectCluster',
              'clusters' => $clusters,
              'user' => $user
          ]);
      } catch (\Exception $e) {
          Log::error('Error in selectCluster: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
          return view('scrn', [
              'Page' => 'EcsaPerformanceReports.IndPerfSelectCluster',
              'error' => 'An error occurred while retrieving clusters. Please try again later.',
              'errorDetails' => $e->getMessage(),
              'user' => Auth::user(),
              'clusters' => [] // Ensure clusters is always defined
          ]);
      }
  }

  /**
   * Display the timeline selection view
   */
  public function selectTimeline(Request $request)
  {
      // Check authorization
      if ($redirect = $this->checkEcsaHcAuthorization()) {
          return $redirect;
      }

      try {
          $clusterId = $request->input('cluster_id');

          if (empty($clusterId)) {
              return redirect()->route('indicator.select.cluster')
                  ->with('error', 'Please select a cluster first.');
          }

          // Get cluster information
          $cluster = DB::select("
              SELECT * FROM clusters WHERE ClusterID = ?
          ", [$clusterId]);

          if (empty($cluster)) {
              return redirect()->route('indicator.select.cluster')
                  ->with('error', 'Selected cluster not found.');
          }
          $cluster = $cluster[0];

          // Get available timelines
          $timelines = DB::select("
              SELECT id, ReportName, Type, Year, ReportingID, status, ClosingDate, Description
              FROM ecsahc_timelines
              ORDER BY Year DESC, id DESC
          ");

          // Group timelines by year for annual report generation
          $years = DB::select("
              SELECT DISTINCT Year
              FROM ecsahc_timelines
              ORDER BY Year DESC
          ");

          // Get timeline types for filtering
          $timelineTypes = DB::select("
              SELECT DISTINCT Type
              FROM ecsahc_timelines
              ORDER BY Type
          ");

          // Verify data integrity
          if (empty($timelines)) {
              return view('scrn', [
                  'Page' => 'EcsaPerformanceReports.IndPerfSelectTimeline',
                  'clusterId' => $clusterId,
                  'cluster' => $cluster,
                  'timelines' => [],
                  'years' => [],
                  'timelineTypes' => [],
                  'error' => 'No reporting timelines found. Please contact the administrator.',
                  'user' => Auth::user(),
                  'organizedTimelines' => [] // Ensure organizedTimelines is always defined
              ]);
          }

          // Group timelines by type and year for better organization
          $organizedTimelines = [];
          foreach ($timelines as $timeline) {
              if (!isset($organizedTimelines[$timeline->Type])) {
                  $organizedTimelines[$timeline->Type] = [];
              }
              if (!isset($organizedTimelines[$timeline->Type][$timeline->Year])) {
                  $organizedTimelines[$timeline->Type][$timeline->Year] = [];
              }
              $organizedTimelines[$timeline->Type][$timeline->Year][] = $timeline;
          }

          return view('scrn', [
              'Page' => 'EcsaPerformanceReports.IndPerfSelectTimeline',
              'clusterId' => $clusterId,
              'cluster' => $cluster,
              'timelines' => $timelines,
              'organizedTimelines' => $organizedTimelines,
              'years' => $years,
              'timelineTypes' => $timelineTypes,
              'user' => Auth::user()
          ]);
      } catch (\Exception $e) {
          Log::error('Error in selectTimeline: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

          // Create a default cluster object if needed
          $defaultCluster = null;
          if (isset($clusterId)) {
              try {
                  $defaultCluster = DB::select("SELECT * FROM clusters WHERE ClusterID = ?", [$clusterId])[0] ?? null;
              } catch (\Exception $ex) {
                  // Ignore any errors here
              }
          }

          if (!$defaultCluster) {
              $defaultCluster = (object)[
                  'ClusterID' => $clusterId ?? 0,
                  'Cluster_Name' => 'Unknown Cluster'
              ];
          }

          return view('scrn', [
              'Page' => 'EcsaPerformanceReports.IndPerfSelectTimeline',
              'clusterId' => $clusterId ?? null,
              'cluster' => $defaultCluster,
              'timelines' => [],
              'organizedTimelines' => [],
              'years' => [],
              'timelineTypes' => [],
              'error' => 'An error occurred while retrieving timelines. Please try again later.',
              'errorDetails' => $e->getMessage(),
              'user' => Auth::user()
          ]);
      }
  }

  /**
   * Generate and display the performance report
   */
  public function generateReport(Request $request)
  {
      // Check authorization
      if ($redirect = $this->checkEcsaHcAuthorization()) {
          return $redirect;
      }

      try {
          $clusterId = $request->input('cluster_id');
          $timelineId = $request->input('timeline_id');
          $reportYear = $request->input('report_year');
          $reportType = $request->input('report_type', 'specific'); // 'specific' or 'annual'
          $timelineType = $request->input('timeline_type'); // Optional filter for timeline type
          $strategicObjectiveFilter = $request->input('strategic_objective'); // Optional filter for SO
          $performanceCategoryFilter = $request->input('performance_category'); // Optional filter for performance category

          // Validate required inputs
          if (empty($clusterId)) {
              return redirect()->route('indicator.select.cluster')
                  ->with('error', 'Please select a cluster first.');
          }

          if ($reportType === 'specific' && empty($timelineId)) {
              return redirect()->route('indicator.select.timeline', ['cluster_id' => $clusterId])
                  ->with('error', 'Please select a timeline for specific report.');
          }

          if ($reportType === 'annual' && empty($reportYear)) {
              return redirect()->route('indicator.select.timeline', ['cluster_id' => $clusterId])
                  ->with('error', 'Please select a year for annual report.');
          }

          // Get cluster information
          $cluster = DB::select("
              SELECT *
              FROM clusters
              WHERE ClusterID = ?
          ", [$clusterId]);

          if (empty($cluster)) {
              return redirect()->route('indicator.select.cluster')
                  ->with('error', 'Selected cluster not found.');
          }
          $cluster = $cluster[0];

          // Get performance data based on report type
          $performanceData = [];
          $reportTimelines = [];
          $reportTitle = '';

          if ($reportType === 'specific') {
              // Get specific timeline report
              $timeline = DB::select("
                  SELECT *
                  FROM ecsahc_timelines
                  WHERE id = ?
              ", [$timelineId]);

              if (empty($timeline)) {
                  return redirect()->route('indicator.select.timeline', ['cluster_id' => $clusterId])
                      ->with('error', 'Selected timeline not found.');
              }
              $timeline = $timeline[0];

              $reportTimelines = [$timeline];
              $reportTitle = $timeline->ReportName;
              $performanceData = $this->getPerformanceData($clusterId, $timeline->ReportingID);
          } else {
              // Get annual report data
              $query = "
                  SELECT *
                  FROM ecsahc_timelines
                  WHERE Year = ?
              ";
              $params = [$reportYear];

              // Add timeline type filter if provided
              if (!empty($timelineType)) {
                  $query .= " AND Type = ?";
                  $params[] = $timelineType;
              }

              $query .= " ORDER BY id";
              $timelines = DB::select($query, $params);

              if (empty($timelines)) {
                  return redirect()->route('indicator.select.timeline', ['cluster_id' => $clusterId])
                      ->with('error', 'No timelines found for selected year and type.');
              }

              $reportTimelines = $timelines;
              $reportTitle = "Annual Performance Report for {$reportYear}";
              if (!empty($timelineType)) {
                  $reportTitle .= " ({$timelineType})";
              }

              // Get consolidated performance data for all timelines in the year
              $performanceData = $this->getAnnualPerformanceData($clusterId, $timelines);
          }

          // Calculate performance scores and prepare chart data
          $performanceResults = $this->calculatePerformanceScores($performanceData);

          // Apply filters if provided
          if (!empty($strategicObjectiveFilter) || !empty($performanceCategoryFilter)) {
              $performanceResults = $this->applyFilters(
                  $performanceResults,
                  $strategicObjectiveFilter,
                  $performanceCategoryFilter
              );
          }

          // Perform data accuracy checks
          $dataAccuracyIssues = $this->checkDataAccuracy($performanceResults);

          // Generate AI-driven insights and recommendations
          $insights = $this->generateInsights($performanceResults, $cluster, $reportTimelines);

          // Get historical performance data for trend analysis
          $historicalData = $this->getHistoricalPerformance($clusterId, $performanceResults);

          // Prepare chart data
          $chartData = $this->prepareChartData($performanceResults, $insights, $historicalData);

          // Get strategic objectives for grouping indicators
          // Fix: Use the correct column names from the database
          try {
              $strategicObjectives = DB::select("
                  SELECT id as SO_ID, name as SO_Name, description as Description, number as SO_Number
                  FROM strategic_objectives
                  ORDER BY number
              ");
          } catch (\Exception $e) {
              // If the above query fails, try an alternative query structure
              // This is a fallback in case the column names are different
              Log::warning('First strategic objectives query failed, trying alternative: ' . $e->getMessage());

              try {
                  // Try to get the actual column names from the table
                  $columns = DB::select("SHOW COLUMNS FROM strategic_objectives");
                  $columnNames = array_map(function($col) { return $col->Field; }, $columns);

                  // Construct a query based on actual column names
                  $idColumn = in_array('id', $columnNames) ? 'id' : (in_array('SO_ID', $columnNames) ? 'SO_ID' : 'strategic_objective_id');
                  $nameColumn = in_array('name', $columnNames) ? 'name' : (in_array('SO_Name', $columnNames) ? 'SO_Name' : 'strategic_objective_name');
                  $descColumn = in_array('description', $columnNames) ? 'description' : (in_array('Description', $columnNames) ? 'Description' : 'strategic_objective_description');
                  $numColumn = in_array('number', $columnNames) ? 'number' : (in_array('SO_Number', $columnNames) ? 'SO_Number' : 'strategic_objective_number');

                  $query = "SELECT $idColumn as SO_ID, $nameColumn as SO_Name, $descColumn as Description, $numColumn as SO_Number FROM strategic_objectives ORDER BY $numColumn";
                  $strategicObjectives = DB::select($query);
              } catch (\Exception $e2) {
                  // If all attempts fail, create a default set of strategic objectives
                  Log::error('All strategic objectives queries failed: ' . $e2->getMessage());
                  $strategicObjectives = [];

                  // Extract unique SO_IDs from performance results to create default SOs
                  $uniqueSoIds = [];
                  foreach ($performanceResults as $result) {
                      $soId = $result['indicator']['SO_ID'] ?? null;
                      if ($soId && !isset($uniqueSoIds[$soId])) {
                          $uniqueSoIds[$soId] = true;
                          $strategicObjectives[] = (object)[
                              'SO_ID' => $soId,
                              'SO_Name' => "Strategic Objective $soId",
                              'Description' => "No description available",
                              'SO_Number' => $soId
                          ];
                      }
                  }
              }
          }

          // Convert to associative array keyed by SO_ID
          $soMap = [];
          foreach ($strategicObjectives as $so) {
              $soMap[$so->SO_ID] = $so;
          }

          // Group performance results by strategic objective for easier display
          $resultsBySO = [];
          foreach ($performanceResults as $result) {
              $soId = $result['indicator']['SO_ID'];
              if (!isset($resultsBySO[$soId])) {
                  $resultsBySO[$soId] = [
                      'so' => $soMap[$soId] ?? (object)[
                          'SO_ID' => $soId,
                          'SO_Name' => "Strategic Objective $soId",
                          'Description' => "No description available",
                          'SO_Number' => $soId
                      ],
                      'indicators' => []
                  ];
              }
              $resultsBySO[$soId]['indicators'][] = $result;
          }

          // Get all available performance categories for filtering
          $performanceCategories = [
              'Met', 'On Track', 'In Progress', 'Not Performing',
              'No Data', 'No Target', 'Invalid Target'
          ];

          return view('scrn', [
              'Page' => 'EcsaPerformanceReports.V2IndicatorPerformance',
              'cluster' => $cluster,
              'timelines' => $reportTimelines,
              'reportTitle' => $reportTitle,
              'performanceResults' => $performanceResults,
              'resultsBySO' => $resultsBySO,
              'chartData' => $chartData,
              'strategicObjectives' => $soMap,
              'dataAccuracyIssues' => $dataAccuracyIssues,
              'insights' => $insights,
              'historicalData' => $historicalData,
              'reportType' => $reportType,
              'reportYear' => $reportYear,
              'timelineType' => $timelineType,
              'strategicObjectiveFilter' => $strategicObjectiveFilter,
              'performanceCategoryFilter' => $performanceCategoryFilter,
              'performanceCategories' => $performanceCategories,
              'user' => Auth::user(),
              'filters' => [
                  'strategicObjectives' => $strategicObjectives,
                  'performanceCategories' => $performanceCategories
              ]
          ]);
      } catch (\Exception $e) {
          Log::error('Error in generateReport: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

          // Create default objects to prevent null errors in the view
          $defaultCluster = null;
          if (isset($clusterId)) {
              try {
                  $defaultCluster = DB::select("SELECT * FROM clusters WHERE ClusterID = ?", [$clusterId])[0] ?? null;
              } catch (\Exception $ex) {
                  // Ignore any errors here
              }
          }

          if (!$defaultCluster) {
              $defaultCluster = (object)[
                  'ClusterID' => $clusterId ?? 0,
                  'Cluster_Name' => 'Unknown Cluster'
              ];
          }

          // Create a default timeline if needed
          $defaultTimeline = (object)[
              'id' => 0,
              'ReportName' => 'Unknown Timeline',
              'Year' => date('Y'),
              'Type' => 'Unknown',
              'ReportingID' => 0,
              'status' => 'Error',
              'ClosingDate' => null,
              'Description' => 'Error occurred while loading timeline data'
          ];

          return view('scrn', [
              'Page' => 'EcsaPerformanceReports.V2IndicatorPerformance',
              'error' => 'An error occurred while generating the report. Please try again later.',
              'errorDetails' => $e->getMessage(),
              'user' => Auth::user(),
              'cluster' => $defaultCluster,
              'timelines' => [$defaultTimeline],
              'reportTitle' => 'Error Report',
              'performanceResults' => [],
              'resultsBySO' => [],
              'chartData' => [],
              'strategicObjectives' => [],
              'dataAccuracyIssues' => [
                  'all' => [],
                  'grouped' => ['error' => [], 'warning' => [], 'info' => []],
                  'count' => 0,
                  'summary' => ['error' => 0, 'warning' => 0, 'info' => 0]
              ],
              'insights' => [
                  'summary' => [],
                  'recommendations' => [],
                  'trends' => [],
                  'anomalies' => [],
                  'strategicObjectives' => []
              ],
              'historicalData' => ['byYear' => [], 'byIndicator' => [], 'byStrategicObjective' => []],
              'reportType' => $request->input('report_type', 'specific'),
              'reportYear' => $request->input('report_year', date('Y')),
              'timelineType' => $request->input('timeline_type', ''),
              'strategicObjectiveFilter' => $request->input('strategic_objective', ''),
              'performanceCategoryFilter' => $request->input('performance_category', ''),
              'performanceCategories' => ['Met', 'On Track', 'In Progress', 'Not Performing', 'No Data', 'No Target', 'Invalid Target'],
              'filters' => [
                  'strategicObjectives' => [],
                  'performanceCategories' => ['Met', 'On Track', 'In Progress', 'Not Performing', 'No Data', 'No Target', 'Invalid Target']
              ]
          ]);
      }
  }

  /**
   * Apply filters to performance results
   */
  private function applyFilters($performanceResults, $strategicObjectiveFilter, $performanceCategoryFilter)
  {
      $filteredResults = [];

      foreach ($performanceResults as $result) {
          $soId = $result['indicator']['SO_ID'];
          $category = $result['performanceCategory'];

          $soMatch = empty($strategicObjectiveFilter) || $soId === $strategicObjectiveFilter;
          $categoryMatch = empty($performanceCategoryFilter) || $category === $performanceCategoryFilter;

          if ($soMatch && $categoryMatch) {
              $filteredResults[] = $result;
          }
      }

      return $filteredResults;
  }

  /**
   * Get performance data for a specific timeline
   */
  private function getPerformanceData($clusterId, $reportingId)
  {
      // Get all indicators for the cluster
      $indicators = $this->getClusterIndicators($clusterId);

      if (empty($indicators)) {
          return [];
      }

      $indicatorIds = array_map(function($indicator) {
          return $indicator['id'];
      }, $indicators);

      $placeholders = implode(',', array_fill(0, count($indicatorIds), '?'));

      // Get performance mappings for the indicators
      $params = array_merge([$clusterId, $reportingId], $indicatorIds);
      $performanceMappings = DB::select("
          SELECT IndicatorID, Response, ResponseType, ReportingComment, created_at, updated_at, SO_ID, UserID
          FROM cluster_performance_mappings
          WHERE ClusterID = ?
          AND ReportingID = ?
          AND IndicatorID IN ({$placeholders})
      ", $params);

      // Get targets for the indicators
      $targetParams = array_merge([$clusterId], $indicatorIds);
      $targets = DB::select("
          SELECT IndicatorID, Target_Value, ResponseType, Target_Year, Baseline2024
          FROM cluster_indicator_targets
          WHERE ClusterID = ?
          AND IndicatorID IN ({$placeholders})
      ", $targetParams);

      // Combine indicators, performance mappings, and targets
      $performanceData = [];
      foreach ($indicators as $indicator) {
          $indicatorId = $indicator['id'];

          // Find performance mapping for this indicator
          $mapping = null;
          foreach ($performanceMappings as $pm) {
              if ($pm->IndicatorID == $indicatorId) {
                  $mapping = (array)$pm;
                  break;
              }
          }

          // Find all targets for this indicator (for trend analysis)
          $indicatorTargets = [];
          foreach ($targets as $t) {
              if ($t->IndicatorID == $indicatorId) {
                  $indicatorTargets[] = (array)$t;
              }
          }

          // Sort targets by year (descending)
          usort($indicatorTargets, function($a, $b) {
              return $b['Target_Year'] - $a['Target_Year'];
          });

          // Use the most recent target
          $target = !empty($indicatorTargets) ? $indicatorTargets[0] : null;

          $performanceData[] = [
              'indicator' => $indicator,
              'mapping' => $mapping,
              'target' => $target,
              'allTargets' => $indicatorTargets // Include all targets for trend analysis
          ];
      }

      return $performanceData;
  }

  /**
   * Get annual performance data across multiple timelines
   * Refactored to properly handle different timeline types and use closing dates
   */
  private function getAnnualPerformanceData($clusterId, $timelines)
  {
      // Get all indicators for the cluster
      $indicators = $this->getClusterIndicators($clusterId);

      if (empty($indicators)) {
          return [];
      }

      $indicatorIds = array_map(function($indicator) {
          return $indicator['id'];
      }, $indicators);

      // Collect reporting IDs from timelines
      $reportingIds = array_map(function($timeline) {
          return $timeline->ReportingID;
      }, $timelines);

      $indicatorPlaceholders = implode(',', array_fill(0, count($indicatorIds), '?'));
      $reportingPlaceholders = implode(',', array_fill(0, count($reportingIds), '?'));

      // Get performance mappings for all timelines
      $params = array_merge([$clusterId], $reportingIds, $indicatorIds);
      $performanceMappings = DB::select("
          SELECT IndicatorID, Response, ResponseType, ReportingComment, ReportingID, created_at, updated_at, SO_ID, UserID
          FROM cluster_performance_mappings
          WHERE ClusterID = ?
          AND ReportingID IN ({$reportingPlaceholders})
          AND IndicatorID IN ({$indicatorPlaceholders})
      ", $params);

      // Get targets for the indicators
      $targetParams = array_merge([$clusterId], $indicatorIds);
      $targets = DB::select("
          SELECT IndicatorID, Target_Value, ResponseType, Target_Year, Baseline2024
          FROM cluster_indicator_targets
          WHERE ClusterID = ?
          AND IndicatorID IN ({$indicatorPlaceholders})
      ", $targetParams);

      // Create a mapping of timeline ReportingID to timeline object
      $timelineMap = [];
      foreach ($timelines as $timeline) {
          $timelineMap[$timeline->ReportingID] = $timeline;
      }

      // Group performance mappings by indicator and timeline type
      $mappingsByIndicator = [];
      foreach ($performanceMappings as $mapping) {
          $indicatorId = $mapping->IndicatorID;
          $reportingId = $mapping->ReportingID;

          // Get the timeline type for this mapping
          $timeline = $timelineMap[$reportingId] ?? null;
          if (!$timeline) continue;

          $timelineType = $timeline->Type;

          if (!isset($mappingsByIndicator[$indicatorId])) {
              $mappingsByIndicator[$indicatorId] = [];
          }

          if (!isset($mappingsByIndicator[$indicatorId][$timelineType])) {
              $mappingsByIndicator[$indicatorId][$timelineType] = [];
          }

          // Add the timeline object to the mapping for access to closing date
          $mapping->timeline = $timeline;

          $mappingsByIndicator[$indicatorId][$timelineType][] = $mapping;
      }

      // Combine indicators, aggregated performance mappings, and targets
      $performanceData = [];
      foreach ($indicators as $indicator) {
          $indicatorId = $indicator['id'];
          $aggregatedMapping = null;

          // Find all targets for this indicator
          $indicatorTargets = [];
          foreach ($targets as $t) {
              if ($t->IndicatorID == $indicatorId) {
                  $indicatorTargets[] = (array)$t;
              }
          }

          // Sort targets by year (descending)
          usort($indicatorTargets, function($a, $b) {
              return $b['Target_Year'] - $a['Target_Year'];
          });

          // Use the most recent target
          $target = !empty($indicatorTargets) ? $indicatorTargets[0] : null;

          // Check if we have any mappings for this indicator
          if (isset($mappingsByIndicator[$indicatorId])) {
              $indicatorMappings = $mappingsByIndicator[$indicatorId];

              // Aggregate based on response type and timeline type
              if ($indicator['ResponseType'] === 'Number') {
                  // For numeric indicators, aggregate differently based on timeline type
                  $aggregatedValue = 0;
                  $count = 0;
                  $latestComment = '';
                  $latestTimestamp = 0;
                  $aggregationDetails = [
                      'method' => 'Aggregated based on timeline types',
                      'timelineTypes' => array_keys($indicatorMappings),
                      'sources' => []
                  ];

                  // Process each timeline type separately
                  foreach ($indicatorMappings as $timelineType => $mappings) {
                      // Sort mappings by closing date (most recent first)
                      // This is the enhanced logic to use closing dates
                      usort($mappings, function($a, $b) {
                          $aDate = $a->timeline->ClosingDate ? strtotime($a->timeline->ClosingDate) : strtotime($a->updated_at);
                          $bDate = $b->timeline->ClosingDate ? strtotime($b->timeline->ClosingDate) : strtotime($b->updated_at);
                          return $bDate - $aDate;
                      });

                      // Track the latest mapping for comment purposes
                      if (!empty($mappings)) {
                          $latestMappingDate = $mappings[0]->timeline->ClosingDate
                              ? strtotime($mappings[0]->timeline->ClosingDate)
                              : strtotime($mappings[0]->updated_at);

                          if ($latestMappingDate > $latestTimestamp) {
                              $latestComment = $mappings[0]->ReportingComment;
                              $latestTimestamp = $latestMappingDate;
                          }
                      }

                      // Different aggregation logic based on timeline type
                      if ($timelineType === 'Quarterly Reports') {
                          // For quarterly reports, sum all values
                          $typeTotal = 0;
                          $typeCount = 0;
                          foreach ($mappings as $mapping) {
                              $typeTotal += (float)$mapping->Response;
                              $typeCount++;
                          }

                          if ($typeCount > 0) {
                              $aggregationDetails['sources'][] = [
                                  'type' => 'Quarterly Reports',
                                  'count' => $typeCount,
                                  'total' => $typeTotal,
                                  'contribution' => $typeTotal
                              ];

                              $aggregatedValue += $typeTotal;
                              $count += $typeCount;
                          }
                      } else if ($timelineType === 'Bi-Annual Reports') {
                          // For bi-annual reports, take the sum of the latest values
                          // (assuming 2 bi-annual reports per year)
                          if (!empty($mappings)) {
                              $typeTotal = (float)$mappings[0]->Response;

                              $aggregationDetails['sources'][] = [
                                  'type' => 'Bi-Annual Reports',
                                  'count' => 1,
                                  'value' => $typeTotal,
                                  'contribution' => $typeTotal,
                                  'reportName' => $mappings[0]->timeline->ReportName,
                                  'closingDate' => $mappings[0]->timeline->ClosingDate
                              ];

                              $aggregatedValue += $typeTotal;
                              $count++;
                          }
                      } else if ($timelineType === 'Annual Reports') {
                          // For annual reports, take the latest value
                          if (!empty($mappings)) {
                              $annualValue = (float)$mappings[0]->Response;

                              $aggregationDetails['sources'][] = [
                                  'type' => 'Annual Reports',
                                  'value' => $annualValue,
                                  'reportName' => $mappings[0]->timeline->ReportName,
                                  'closingDate' => $mappings[0]->timeline->ClosingDate
                              ];

                              // Annual report takes precedence - use this value directly
                              $aggregatedValue = $annualValue;
                              $count = 1;
                              $aggregationDetails['method'] = 'Using Annual Report value (takes precedence)';
                              break; // Annual report takes precedence
                          }
                      } else {
                          // For other timeline types, use the latest value
                          if (!empty($mappings)) {
                              $otherValue = (float)$mappings[0]->Response;

                              $aggregationDetails['sources'][] = [
                                  'type' => $timelineType,
                                  'value' => $otherValue,
                                  'reportName' => $mappings[0]->timeline->ReportName,
                                  'closingDate' => $mappings[0]->timeline->ClosingDate
                              ];

                              $aggregatedValue += $otherValue;
                              $count++;
                          }
                      }
                  }

                  if ($count > 0) {
                      $aggregatedMapping = [
                          'IndicatorID' => $indicatorId,
                          'Response' => $aggregatedValue,
                          'ResponseType' => 'Number',
                          'ReportingComment' => $latestComment ?: 'Aggregated from multiple reports',
                          'aggregationDetails' => $aggregationDetails
                      ];
                  }
              } else if ($indicator['ResponseType'] === 'Yes/No' || $indicator['ResponseType'] === 'Boolean') {
                  // For boolean indicators, use the most recent value across all timeline types
                  $latestMapping = null;
                  $latestTimestamp = 0;
                  $latestTimelineType = '';
                  $latestReportName = '';
                  $latestClosingDate = null;

                  foreach ($indicatorMappings as $timelineType => $mappings) {
                      foreach ($mappings as $mapping) {
                          $mappingDate = $mapping->timeline->ClosingDate
                              ? strtotime($mapping->timeline->ClosingDate)
                              : strtotime($mapping->updated_at);

                          if ($mappingDate > $latestTimestamp) {
                              $latestMapping = $mapping;
                              $latestTimestamp = $mappingDate;
                              $latestTimelineType = $timelineType;
                              $latestReportName = $mapping->timeline->ReportName;
                              $latestClosingDate = $mapping->timeline->ClosingDate;
                          }
                      }
                  }

                  if ($latestMapping) {
                      $aggregatedMapping = (array)$latestMapping;
                      $aggregatedMapping['aggregationDetails'] = [
                          'method' => 'Most recent value',
                          'timestamp' => date('Y-m-d H:i:s', $latestTimestamp),
                          'timelineType' => $latestTimelineType,
                          'reportName' => $latestReportName,
                          'closingDate' => $latestClosingDate
                      ];
                  }
              } else {
                  // For text indicators, use the most recent value across all timeline types
                  $latestMapping = null;
                  $latestTimestamp = 0;
                  $latestTimelineType = '';
                  $latestReportName = '';
                  $latestClosingDate = null;

                  foreach ($indicatorMappings as $timelineType => $mappings) {
                      foreach ($mappings as $mapping) {
                          $mappingDate = $mapping->timeline->ClosingDate
                              ? strtotime($mapping->timeline->ClosingDate)
                              : strtotime($mapping->updated_at);

                          if ($mappingDate > $latestTimestamp) {
                              $latestMapping = $mapping;
                              $latestTimestamp = $mappingDate;
                              $latestTimelineType = $timelineType;
                              $latestReportName = $mapping->timeline->ReportName;
                              $latestClosingDate = $mapping->timeline->ClosingDate;
                          }
                      }
                  }

                  if ($latestMapping) {
                      $aggregatedMapping = (array)$latestMapping;
                      $aggregatedMapping['aggregationDetails'] = [
                          'method' => 'Most recent value',
                          'timestamp' => date('Y-m-d H:i:s', $latestTimestamp),
                          'timelineType' => $latestTimelineType,
                          'reportName' => $latestReportName,
                          'closingDate' => $latestClosingDate
                      ];
                  }
              }
          }

          $performanceData[] = [
              'indicator' => $indicator,
              'mapping' => $aggregatedMapping,
              'target' => $target,
              'allTargets' => $indicatorTargets, // Include all targets for trend analysis
              'rawMappings' => $mappingsByIndicator[$indicatorId] ?? [] // Include raw mappings for detailed analysis
          ];
      }

      return $performanceData;
  }

  /**
   * Get all indicators for a cluster
   */
  private function getClusterIndicators($clusterId)
  {
      $indicators = DB::select("
          SELECT id, SO_ID, Indicator_Number, Indicator_Name, ResponseType, Responsible_Cluster
          FROM performance_indicators
          ORDER BY SO_ID, Indicator_Number
      ");

      $clusterIndicators = [];
      foreach ($indicators as $indicator) {
          $responsibleClusters = json_decode($indicator->Responsible_Cluster, true);

          if (in_array($clusterId, $responsibleClusters)) {
              $clusterIndicators[] = (array)$indicator;
          }
      }

      return $clusterIndicators;
  }

  /**
   * Calculate performance scores based on targets and actual values
   */
  private function calculatePerformanceScores($performanceData)
  {
      $results = [];

      foreach ($performanceData as $data) {
          $indicator = $data['indicator'];
          $mapping = $data['mapping'];
          $target = $data['target'];
          $allTargets = $data['allTargets'] ?? [];
          $rawMappings = $data['rawMappings'] ?? [];

          $score = null;
          $percentageAchieved = null;
          $performanceCategory = null;
          $analysisComment = null;
          $trend = null;
          $detailedAnalysis = [];

          // Skip if no mapping (no data reported)
          if (!$mapping) {
              $results[] = [
                  'indicator' => $indicator,
                  'target' => $target,
                  'allTargets' => $allTargets,
                  'actual' => null,
                  'rawMappings' => $rawMappings,
                  'score' => null,
                  'percentageAchieved' => null,
                  'performanceCategory' => 'No Data',
                  'analysisComment' => 'No data reported for this indicator',
                  'trend' => null,
                  'detailedAnalysis' => [
                      'reason' => 'No data reported',
                      'recommendation' => 'Implement data collection for this indicator'
                  ]
              ];
              continue;
          }

          // Skip if no target
          if (!$target) {
              $results[] = [
                  'indicator' => $indicator,
                  'target' => null,
                  'allTargets' => $allTargets,
                  'actual' => $mapping,
                  'rawMappings' => $rawMappings,
                  'score' => null,
                  'percentageAchieved' => null,
                  'performanceCategory' => 'No Target',
                  'analysisComment' => 'No target set for this indicator',
                  'trend' => null,
                  'detailedAnalysis' => [
                      'reason' => 'No target set',
                      'recommendation' => 'Set a target for this indicator to enable performance tracking',
                      'actualValue' => $mapping['Response']
                  ]
              ];
              continue;
          }

          // Calculate score based on response type
          switch ($indicator['ResponseType']) {
              case 'Number':
                  // For numeric indicators, calculate percentage of target achieved
                  $actualValue = (float)$mapping['Response'];
                  $targetValue = (float)$target['Target_Value'];
                  $baseline = isset($target['Baseline2024']) ? (float)$target['Baseline2024'] : null;

                  $detailedAnalysis = [
                      'actualValue' => $actualValue,
                      'targetValue' => $targetValue,
                      'baseline' => $baseline,
                      'aggregationDetails' => $mapping['aggregationDetails'] ?? null
                  ];

                  if ($targetValue > 0) {
                      $percentageAchieved = ($actualValue / $targetValue) * 100;
                      $score = $percentageAchieved;

                      // Determine performance category
                      if ($percentageAchieved < 10) {
                          $performanceCategory = 'Not Performing';
                          $detailedAnalysis['status'] = 'Critical underperformance';
                          $detailedAnalysis['recommendation'] = 'Urgent intervention required';
                      } elseif ($percentageAchieved >= 10 && $percentageAchieved < 50) {
                          $performanceCategory = 'In Progress';
                          $detailedAnalysis['status'] = 'Significant gap to target';
                          $detailedAnalysis['recommendation'] = 'Accelerate implementation';
                      } elseif ($percentageAchieved >= 50 && $percentageAchieved < 90) {
                          $performanceCategory = 'On Track';
                          $detailedAnalysis['status'] = 'Good progress toward target';
                          $detailedAnalysis['recommendation'] = 'Continue current implementation strategy';
                      } else {
                          $performanceCategory = 'Met';
                          $detailedAnalysis['status'] = 'Target achieved or exceeded';
                          $detailedAnalysis['recommendation'] = 'Maintain performance and consider setting more ambitious targets';
                      }

                      // Calculate trend compared to baseline if available
                      if ($baseline !== null && $baseline > 0) {
                          $changeFromBaseline = (($actualValue - $baseline) / $baseline) * 100;
                          $trend = [
                              'value' => $changeFromBaseline,
                              'direction' => $changeFromBaseline >= 0 ? 'up' : 'down',
                              'positive' => $changeFromBaseline >= 0, // Is this a positive trend?
                              'baseline' => $baseline
                          ];

                          $detailedAnalysis['trend'] = $trend;

                          $analysisComment = sprintf(
                              "Achieved %.1f%% of target (%.1f/%.1f). %s baseline by %.1f%%.",
                              $percentageAchieved,
                              $actualValue,
                              $targetValue,
                              $changeFromBaseline >= 0 ? 'Improved from' : 'Decreased from',
                              abs($changeFromBaseline)
                          );
                      } else {
                          $analysisComment = sprintf(
                              "Achieved %.1f%% of target (%.1f/%.1f).",
                              $percentageAchieved,
                              $actualValue,
                              $targetValue
                          );
                      }

                      // Add year-over-year trend if multiple targets exist
                      if (count($allTargets) > 1) {
                          $detailedAnalysis['yearOverYearTargets'] = $allTargets;
                      }
                  } else {
                      $performanceCategory = 'Invalid Target';
                      $analysisComment = 'Target value must be greater than zero';
                      $detailedAnalysis['error'] = 'Invalid target value (zero or negative)';
                      $detailedAnalysis['recommendation'] = 'Set a positive target value';
                  }
                  break;

              case 'Yes/No':
              case 'Boolean':
                  // For boolean indicators, check if the response matches the target
                  $actualValue = strtolower($mapping['Response']);
                  $targetValue = strtolower($target['Target_Value']);

                  $detailedAnalysis = [
                      'actualValue' => $actualValue,
                      'targetValue' => $targetValue,
                      'aggregationDetails' => $mapping['aggregationDetails'] ?? null
                  ];

                  $isMatch = ($actualValue == $targetValue ||
                             ($actualValue == '1' && $targetValue == 'yes') ||
                             ($actualValue == 'yes' && $targetValue == '1') ||
                             ($actualValue == '0' && $targetValue == 'no') ||
                             ($actualValue == 'no' && $targetValue == '0'));

                  if ($isMatch) {
                      $score = 100;
                      $percentageAchieved = 100;
                      $performanceCategory = 'Met';
                      $analysisComment = 'Target achieved';
                      $detailedAnalysis['status'] = 'Target achieved';
                      $detailedAnalysis['recommendation'] = 'Maintain performance';
                  } else {
                      $score = 0;
                      $percentageAchieved = 0;
                      $performanceCategory = 'Not Performing';
                      $analysisComment = 'Target not achieved';
                      $detailedAnalysis['status'] = 'Target not achieved';
                      $detailedAnalysis['recommendation'] = 'Implement corrective actions';
                  }
                  break;

              case 'Text':
                  // For text indicators, perform qualitative analysis
                  $actualValue = $mapping['Response'];
                  $targetValue = $target['Target_Value'];

                  $detailedAnalysis = [
                      'actualValue' => $actualValue,
                      'targetValue' => $targetValue,
                      'aggregationDetails' => $mapping['aggregationDetails'] ?? null
                  ];

                  // Simple text comparison (could be enhanced with NLP in a real system)
                  if (strtolower($actualValue) == strtolower($targetValue)) {
                      $score = 100;
                      $percentageAchieved = 100;
                      $performanceCategory = 'Met';
                      $analysisComment = 'Exact match with target';
                      $detailedAnalysis['status'] = 'Exact match with target';
                      $detailedAnalysis['recommendation'] = 'Maintain performance';
                  } elseif (strpos(strtolower($actualValue), strtolower($targetValue)) !== false) {
                      $score = 75;
                      $percentageAchieved = 75;
                      $performanceCategory = 'On Track';
                      $analysisComment = 'Partial match with target';
                      $detailedAnalysis['status'] = 'Partial match with target';
                      $detailedAnalysis['recommendation'] = 'Refine implementation to fully meet target';
                  } else {
                      // Calculate similarity (simple approach)
                      similar_text(strtolower($actualValue), strtolower($targetValue), $similarity);
                      $score = $similarity;
                      $percentageAchieved = $similarity;
                      $detailedAnalysis['textSimilarity'] = $similarity;

                      if ($similarity < 10) {
                          $performanceCategory = 'Not Performing';
                          $detailedAnalysis['status'] = 'Minimal alignment with target';
                          $detailedAnalysis['recommendation'] = 'Significant revision needed';
                      } elseif ($similarity >= 10 && $similarity < 50) {
                          $performanceCategory = 'In Progress';
                          $detailedAnalysis['status'] = 'Partial alignment with target';
                          $detailedAnalysis['recommendation'] = 'Continue implementation with adjustments';
                      } elseif ($similarity >= 50 && $similarity < 90) {
                          $performanceCategory = 'On Track';
                          $detailedAnalysis['status'] = 'Good alignment with target';
                          $detailedAnalysis['recommendation'] = 'Minor refinements needed';
                      } else {
                          $performanceCategory = 'Met';
                          $detailedAnalysis['status'] = 'Strong alignment with target';
                          $detailedAnalysis['recommendation'] = 'Maintain performance';
                      }

                      $analysisComment = "Text similarity: {$similarity}%";
                  }
                  break;

              default:
                  $performanceCategory = 'Unknown Type';
                  $analysisComment = 'Unknown response type';
                  $detailedAnalysis['error'] = 'Unknown response type';
                  break;
          }

          $results[] = [
              'indicator' => $indicator,
              'target' => $target,
              'allTargets' => $allTargets,
              'actual' => $mapping,
              'rawMappings' => $rawMappings,
              'score' => $score,
              'percentageAchieved' => $percentageAchieved,
              'performanceCategory' => $performanceCategory,
              'analysisComment' => $analysisComment,
              'trend' => $trend,
              'detailedAnalysis' => $detailedAnalysis
          ];
      }

      return $results;
  }

  /**
   * Get historical performance data for trend analysis
   */
  private function getHistoricalPerformance($clusterId, $performanceResults)
  {
      $historicalData = [
          'byYear' => [],
          'byIndicator' => [],
          'byStrategicObjective' => []
      ];

      // Get indicators from current results
      $indicatorIds = [];
      foreach ($performanceResults as $result) {
          if (isset($result['indicator']['id'])) {
              $indicatorIds[] = $result['indicator']['id'];
          }
      }

      if (empty($indicatorIds)) {
          return $historicalData;
      }

      $placeholders = implode(',', array_fill(0, count($indicatorIds), '?'));

      // Get all timelines for the past 3 years
      $currentYear = date('Y');
      $pastYears = [$currentYear, $currentYear - 1, $currentYear - 2];
      $yearPlaceholders = implode(',', array_fill(0, count($pastYears), '?'));

      $timelines = DB::select("
          SELECT id, ReportName, Type, Year, ReportingID, ClosingDate
          FROM ecsahc_timelines
          WHERE Year IN ({$yearPlaceholders})
          ORDER BY Year DESC, id DESC
      ", $pastYears);

      if (empty($timelines)) {
          return $historicalData;
      }

      // Group timelines by year
      $timelinesByYear = [];
      foreach ($timelines as $timeline) {
          if (!isset($timelinesByYear[$timeline->Year])) {
              $timelinesByYear[$timeline->Year] = [];
          }
          $timelinesByYear[$timeline->Year][] = $timeline;
      }

      // Get historical performance data for each year
      foreach ($timelinesByYear as $year => $yearTimelines) {
          $reportingIds = array_map(function($timeline) {
              return $timeline->ReportingID;
          }, $yearTimelines);

          $reportingPlaceholders = implode(',', array_fill(0, count($reportingIds), '?'));

          // Get performance mappings for this year
          $params = array_merge([$clusterId], $reportingIds, $indicatorIds);
          $performanceMappings = DB::select("
              SELECT IndicatorID, Response, ResponseType, ReportingID, SO_ID
              FROM cluster_performance_mappings
              WHERE ClusterID = ?
              AND ReportingID IN ({$reportingPlaceholders})
              AND IndicatorID IN ({$placeholders})
          ", $params);

          // Group by indicator
          $mappingsByIndicator = [];
          $mappingsBySO = [];

          foreach ($performanceMappings as $mapping) {
              $indicatorId = $mapping->IndicatorID;
              $soId = $mapping->SO_ID;

              if (!isset($mappingsByIndicator[$indicatorId])) {
                  $mappingsByIndicator[$indicatorId] = [];
              }
              $mappingsByIndicator[$indicatorId][] = $mapping;

              if (!empty($soId)) {
                  if (!isset($mappingsBySO[$soId])) {
                      $mappingsBySO[$soId] = [];
                  }
                  $mappingsBySO[$soId][] = $mapping;
              }
          }

          // Calculate average performance for each indicator for this year
          $yearPerformance = [
              'indicators' => [],
              'strategicObjectives' => []
          ];

          // Process indicators
          foreach ($indicatorIds as $indicatorId) {
              if (isset($mappingsByIndicator[$indicatorId]) && !empty($mappingsByIndicator[$indicatorId])) {
                  $mappings = $mappingsByIndicator[$indicatorId];
                  $totalResponse = 0;
                  $count = 0;

                  foreach ($mappings as $mapping) {
                      if ($mapping->ResponseType === 'Number') {
                          $totalResponse += (float)$mapping->Response;
                          $count++;
                      }
                  }

                  if ($count > 0) {
                      $yearPerformance['indicators'][$indicatorId] = [
                          'value' => $totalResponse / $count,
                          'count' => $count
                      ];
                  }
              }
          }

          // Process strategic objectives
          foreach ($mappingsBySO as $soId => $mappings) {
              $totalResponse = 0;
              $count = 0;

              foreach ($mappings as $mapping) {
                  if ($mapping->ResponseType === 'Number') {
                      $totalResponse += (float)$mapping->Response;
                      $count++;
                  }
              }

              if ($count > 0) {
                  $yearPerformance['strategicObjectives'][$soId] = [
                      'value' => $totalResponse / $count,
                      'count' => $count
                  ];
              }
          }

          $historicalData['byYear'][$year] = $yearPerformance;
      }

      // Reorganize data by indicator for easier trend analysis
      foreach ($indicatorIds as $indicatorId) {
          $indicatorTrend = [];

          foreach ($historicalData['byYear'] as $year => $yearData) {
              if (isset($yearData['indicators'][$indicatorId])) {
                  $indicatorTrend[$year] = $yearData['indicators'][$indicatorId]['value'];
              }
          }

          if (!empty($indicatorTrend)) {
              $historicalData['byIndicator'][$indicatorId] = $indicatorTrend;
          }
      }

      // Reorganize data by strategic objective
      $allSOs = [];
      foreach ($historicalData['byYear'] as $yearData) {
          foreach (array_keys($yearData['strategicObjectives']) as $soId) {
              $allSOs[$soId] = true;
          }
      }

      foreach (array_keys($allSOs) as $soId) {
          $soTrend = [];

          foreach ($historicalData['byYear'] as $year => $yearData) {
              if (isset($yearData['strategicObjectives'][$soId])) {
                  $soTrend[$year] = $yearData['strategicObjectives'][$soId]['value'];
              }
          }

          if (!empty($soTrend)) {
              $historicalData['byStrategicObjective'][$soId] = $soTrend;
          }
      }

      return $historicalData;
  }

  /**
   * Prepare data for charts
   */
  private function prepareChartData($performanceResults, $insights, $historicalData)
  {
      // Group indicators by strategic objective
      $soPerformance = [];
      $performanceCategories = [
          'Met' => 0,
          'On Track' => 0,
          'In Progress' => 0,
          'Not Performing' => 0,
          'No Data' => 0,
          'No Target' => 0,
          'Invalid Target' => 0
      ];

      // Track top and bottom performers
      $indicatorPerformance = [];

      foreach ($performanceResults as $result) {
          $soId = $result['indicator']['SO_ID'];
          $category = $result['performanceCategory'];
          $indicatorId = $result['indicator']['id'];

          // Count by performance category
          if (isset($performanceCategories[$category])) {
              $performanceCategories[$category]++;
          }

          // Group by strategic objective
          if (!isset($soPerformance[$soId])) {
              $soPerformance[$soId] = [
                  'totalIndicators' => 0,
                  'totalScore' => 0,
                  'categories' => [
                      'Met' => 0,
                      'On Track' => 0,
                      'In Progress' => 0,
                      'Not Performing' => 0,
                      'No Data' => 0,
                      'No Target' => 0,
                      'Invalid Target' => 0
                  ],
                  'indicators' => []
              ];
          }

          $soPerformance[$soId]['totalIndicators']++;

          if ($result['score'] !== null) {
              $soPerformance[$soId]['totalScore'] += $result['score'];

              // Track individual indicator performance for this SO
              $soPerformance[$soId]['indicators'][] = [
                  'id' => $indicatorId,
                  'name' => $result['indicator']['Indicator_Name'],
                  'number' => $result['indicator']['Indicator_Number'],
                  'score' => $result['score'],
                  'category' => $category,
                  'trend' => $result['trend'],
                  'detailedAnalysis' => $result['detailedAnalysis']
              ];

              // Track overall indicator performance for top/bottom analysis
              $indicatorPerformance[] = [
                  'id' => $indicatorId,
                  'name' => $result['indicator']['Indicator_Name'],
                  'number' => $result['indicator']['Indicator_Number'],
                  'score' => $result['score'],
                  'category' => $category,
                  'so_id' => $soId,
                  'trend' => $result['trend'],
                  'detailedAnalysis' => $result['detailedAnalysis']
              ];
          }

          if (isset($soPerformance[$soId]['categories'][$category])) {
              $soPerformance[$soId]['categories'][$category]++;
          }
      }

      // Calculate average score for each strategic objective
      foreach ($soPerformance as $soId => &$data) {
          if ($data['totalIndicators'] > 0) {
              $data['averageScore'] = $data['totalScore'] / $data['totalIndicators'];

              // Sort indicators by score (descending)
              usort($data['indicators'], function($a, $b) {
                  return $b['score'] - $a['score'];
              });

              // Get top and bottom 3 indicators for this SO
              $data['topPerformers'] = array_slice($data['indicators'], 0, 3);
              $data['bottomPerformers'] = array_slice(array_reverse($data['indicators']), 0, 3);
          } else {
              $data['averageScore'] = 0;
              $data['topPerformers'] = [];
              $data['bottomPerformers'] = [];
          }
      }

      // Sort SOs by average score (descending)
      uasort($soPerformance, function($a, $b) {
          return $b['averageScore'] - $a['averageScore'];
      });

      // Get overall top and bottom performers
      usort($indicatorPerformance, function($a, $b) {
          return $b['score'] - $a['score'];
      });

      $topPerformers = array_slice($indicatorPerformance, 0, 5);
      $bottomPerformers = array_slice(array_reverse($indicatorPerformance), 0, 5);

      // Prepare historical trend data for charts
      $trendData = [
          'years' => array_keys($historicalData['byYear']),
          'indicators' => [],
          'strategicObjectives' => []
      ];

      // Format indicator trend data for charts
      foreach ($historicalData['byIndicator'] as $indicatorId => $yearValues) {
          $indicatorInfo = null;

          // Find indicator info
          foreach ($performanceResults as $result) {
              if ($result['indicator']['id'] == $indicatorId) {
                  $indicatorInfo = $result['indicator'];
                  break;
              }
          }

          if ($indicatorInfo) {
              $trendData['indicators'][] = [
                  'id' => $indicatorId,
                  'name' => $indicatorInfo['Indicator_Name'],
                  'number' => $indicatorInfo['Indicator_Number'],
                  'so_id' => $indicatorInfo['SO_ID'],
                  'values' => $yearValues
              ];
          }
      }

      // Format strategic objective trend data for charts
      foreach ($historicalData['byStrategicObjective'] as $soId => $yearValues) {
          $trendData['strategicObjectives'][] = [
              'id' => $soId,
              'values' => $yearValues
          ];
      }

      // Prepare data for charts
      $chartData = [
          'performanceCategories' => $performanceCategories,
          'strategicObjectives' => $soPerformance,
          'topPerformers' => $topPerformers,
          'bottomPerformers' => $bottomPerformers,
          'insights' => $insights,
          'trends' => $trendData,

          // Additional chart data for various visualizations
          'performanceBySO' => array_map(function($so) {
              return [
                  'id' => $so['id'] ?? '',
                  'score' => $so['averageScore'] ?? 0,
                  'categoryBreakdown' => $so['categories'] ?? []
              ];
          }, $soPerformance),

          // Data for radar charts
          'radarData' => [
              'axes' => array_keys($soPerformance),
              'values' => array_map(function($so) {
                  return $so['averageScore'] ?? 0;
              }, $soPerformance)
          ],

          // Data for timeline visualization
          'timelineData' => $trendData
      ];

      return $chartData;
  }

  /**
   * Check data accuracy and identify potential issues
   */
  private function checkDataAccuracy($performanceResults)
  {
      $issues = [];

      foreach ($performanceResults as $result) {
          $indicator = $result['indicator'];
          $mapping = $result['actual'];
          $target = $result['target'];

          // Skip if no data or target
          if (!$mapping || !$target) {
              continue;
          }

          // Check for potential data issues
          switch ($indicator['ResponseType']) {
              case 'Number':
                  $actualValue = (float)$mapping['Response'];
                  $targetValue = (float)$target['Target_Value'];

                  // Check for unusually high values (more than 1000% of target)
                  if ($targetValue > 0 && $actualValue > $targetValue * 10) {
                      $issues[] = [
                          'indicator' => $indicator,
                          'issue' => 'Unusually high value',
                          'severity' => 'warning',
                          'details' => "Reported value ({$actualValue}) is more than 10x the target ({$targetValue})",
                          'recommendation' => 'Verify data accuracy and collection methodology'
                      ];
                  }

                  // Check for negative values when they shouldn't be negative
                  if ($actualValue < 0 && $targetValue >= 0) {
                      $issues[] = [
                          'indicator' => $indicator,
                          'issue' => 'Negative value',
                          'severity' => 'error',
                          'details' => "Reported value ({$actualValue}) is negative while target is non-negative",
                          'recommendation' => 'Correct data entry or verify measurement methodology'
                      ];
                  }

                  // Check for zero values (might be missing data)
                  if ($actualValue === 0.0) {
                      $issues[] = [
                          'indicator' => $indicator,
                          'issue' => 'Zero value',
                          'severity' => 'info',
                          'details' => "Reported value is zero, which might indicate missing data",
                          'recommendation' => 'Verify if this is accurate or if data is missing'
                      ];
                  }

                  // Check for statistical outliers (simplified approach)
                  // In a real system, this would use more sophisticated statistical methods
                  if ($targetValue > 0 && ($actualValue < $targetValue * 0.1 || $actualValue > $targetValue * 2)) {
                      $issues[] = [
                          'indicator' => $indicator,
                          'issue' => 'Potential outlier',
                          'severity' => 'info',
                          'details' => "Reported value ({$actualValue}) is significantly different from target ({$targetValue})",
                          'recommendation' => 'Review data collection methodology and verify accuracy'
                      ];
                  }

                  // Check for inconsistent aggregation (if aggregation details are available)
                  if (isset($mapping['aggregationDetails']) && isset($mapping['aggregationDetails']['count']) && $mapping['aggregationDetails']['count'] > 1) {
                      $issues[] = [
                          'indicator' => $indicator,
                          'issue' => 'Aggregated data',
                          'severity' => 'info',
                          'details' => "This value is aggregated from {$mapping['aggregationDetails']['count']} reports",
                          'recommendation' => 'Consider reviewing individual reports for more detailed analysis'
                      ];
                  }
                  break;

              case 'Yes/No':
              case 'Boolean':
                  $actualValue = strtolower($mapping['Response']);

                  // Check for invalid boolean values
                  if (!in_array($actualValue, ['yes', 'no', '1', '0', 'true', 'false'])) {
                      $issues[] = [
                          'indicator' => $indicator,
                          'issue' => 'Invalid boolean value',
                          'severity' => 'error',
                          'details' => "Reported value ({$actualValue}) is not a valid Yes/No response",
                          'recommendation' => 'Correct data entry to use a valid Yes/No value'
                      ];
                  }
                  break;

              case 'Text':
                  // Check for very short text responses
                  if (strlen($mapping['Response']) < 5) {
                      $issues[] = [
                          'indicator' => $indicator,
                          'issue' => 'Very short text response',
                          'severity' => 'warning',
                          'details' => "Text response is unusually short (" . strlen($mapping['Response']) . " characters)",
                          'recommendation' => 'Provide more detailed information in the response'
                      ];
                  }

                  // Check for potential placeholder text
                  $placeholderPatterns = ['/test/i', '/todo/i', '/placeholder/i', '/tbd/i', '/n\/a/i'];
                  foreach ($placeholderPatterns as $pattern) {
                      if (preg_match($pattern, $mapping['Response'])) {
                          $issues[] = [
                              'indicator' => $indicator,
                              'issue' => 'Potential placeholder text',
                              'severity' => 'warning',
                              'details' => "Response may contain placeholder text: '{$mapping['Response']}'",
                              'recommendation' => 'Replace placeholder text with actual data'
                          ];
                          break;
                      }
                  }
                  break;
          }
      }

      // Group issues by severity for easier handling in the view
      $groupedIssues = [
          'error' => [],
          'warning' => [],
          'info' => []
      ];

      foreach ($issues as $issue) {
          $severity = $issue['severity'] ?? 'info';
          $groupedIssues[$severity][] = $issue;
      }

      return [
          'all' => $issues,
          'grouped' => $groupedIssues,
          'count' => count($issues),
          'summary' => [
              'error' => count($groupedIssues['error']),
              'warning' => count($groupedIssues['warning']),
              'info' => count($groupedIssues['info'])
          ]
      ];
  }

  /**
   * Generate AI-driven insights and recommendations based on performance data
   */
  private function generateInsights($performanceResults, $cluster, $timelines)
  {
      $insights = [
          'summary' => [],
          'recommendations' => [],
          'trends' => [],
          'anomalies' => [],
          'strategicObjectives' => []
      ];

      // Count performance categories
      $categoryCounts = [
          'Met' => 0,
          'On Track' => 0,
          'In Progress' => 0,
          'Not Performing' => 0,
          'No Data' => 0,
          'No Target' => 0,
          'Invalid Target' => 0
      ];

      $totalIndicators = count($performanceResults);
      $indicatorsWithData = 0;
      $indicatorsWithTarget = 0;
      $totalScore = 0;

      // Group by strategic objective
      $soPerformance = [];

      foreach ($performanceResults as $result) {
          $category = $result['performanceCategory'];
          $soId = $result['indicator']['SO_ID'];

          // Count categories
          if (isset($categoryCounts[$category])) {
              $categoryCounts[$category]++;
          }

          // Track indicators with data and targets
          if ($result['actual']) {
              $indicatorsWithData++;
          }

          if ($result['target']) {
              $indicatorsWithTarget++;
          }

          // Track total score
          if ($result['score'] !== null) {
              $totalScore += $result['score'];
          }

          // Group by strategic objective
          if (!isset($soPerformance[$soId])) {
              $soPerformance[$soId] = [
                  'count' => 0,
                  'score' => 0,
                  'withData' => 0,
                  'categories' => array_fill_keys(array_keys($categoryCounts), 0),
                  'indicators' => []
              ];
          }

          $soPerformance[$soId]['count']++;
          $soPerformance[$soId]['categories'][$category]++;
          $soPerformance[$soId]['indicators'][] = $result;

          if ($result['score'] !== null) {
              $soPerformance[$soId]['score'] += $result['score'];
              $soPerformance[$soId]['withData']++;
          }
      }

      // Calculate average score
      $overallScore = $indicatorsWithData > 0 ? $totalScore / $indicatorsWithData : 0;

      // Calculate average score for each SO
      foreach ($soPerformance as $soId => &$data) {
          $data['averageScore'] = $data['withData'] > 0 ? $data['score'] / $data['withData'] : 0;
          $data['dataCompleteness'] = $data['count'] > 0 ? ($data['withData'] / $data['count']) * 100 : 0;

          // Generate SO-specific insights
          $soInsight = [
              'id' => $soId,
              'averageScore' => $data['averageScore'],
              'dataCompleteness' => $data['dataCompleteness'],
              'categoryBreakdown' => $data['categories'],
              'summary' => sprintf(
                  "%s performance: %.1f%% (%s). Data completeness: %.1f%%",
                  $soId,
                  $data['averageScore'],
                  $this->getCategoryForScore($data['averageScore']),
                  $data['dataCompleteness']
              ),
              'recommendations' => []
          ];

          // Add SO-specific recommendations
          if ($data['categories']['No Target'] > 0) {
              $soInsight['recommendations'][] = sprintf(
                  "Set targets for the %d indicators in %s that currently lack targets.",
                  $data['categories']['No Target'],
                  $soId
              );
          }

          if ($data['categories']['No Data'] > 0) {
              $soInsight['recommendations'][] = sprintf(
                  "Improve data collection for the %d indicators in %s without reported data.",
                  $data['categories']['No Data'],
                  $soId
              );
          }

          if ($data['categories']['Not Performing'] > 0) {
              $soInsight['recommendations'][] = sprintf(
                  "Develop intervention strategies for the %d underperforming indicators in %s.",
                  $data['categories']['Not Performing'],
                  $soId
              );
          }

          $insights['strategicObjectives'][$soId] = $soInsight;
      }

      // Sort SOs by average score (descending)
      uasort($soPerformance, function($a, $b) {
          return $b['averageScore'] - $a['averageScore'];
      });

      // Generate summary insights
      $insights['summary'][] = sprintf(
          "Overall performance score: %.1f%% (%s)",
          $overallScore,
          $this->getCategoryForScore($overallScore)
      );

      $insights['summary'][] = sprintf(
          "Data completeness: %.1f%% (%d of %d indicators have data)",
          ($indicatorsWithData / $totalIndicators) * 100,
          $indicatorsWithData,
          $totalIndicators
      );

      $insights['summary'][] = sprintf(
          "Target setting: %.1f%% (%d of %d indicators have targets)",
          ($indicatorsWithTarget / $totalIndicators) * 100,
          $indicatorsWithTarget,
          $totalIndicators
      );

      // Performance category breakdown
      $categoryBreakdown = "Performance category breakdown: ";
      foreach ($categoryCounts as $category => $count) {
          if ($count > 0) {
              $percentage = ($count / $totalIndicators) * 100;
              $categoryBreakdown .= sprintf("%s: %d (%.1f%%), ", $category, $count, $percentage);
          }
      }
      $insights['summary'][] = rtrim($categoryBreakdown, ", ");

      // Top performing strategic objectives
      $topSOs = array_slice($soPerformance, 0, 2, true);
      if (!empty($topSOs)) {
          $topSOsText = "Top performing strategic objectives: ";
          foreach ($topSOs as $soId => $data) {
              $topSOsText .= sprintf("%s (%.1f%%), ", $soId, $data['averageScore']);
          }
          $insights['summary'][] = rtrim($topSOsText, ", ");
      }

      // Bottom performing strategic objectives
      $bottomSOs = array_slice(array_reverse($soPerformance, true), 0, 2, true);
      if (!empty($bottomSOs)) {
          $bottomSOsText = "Areas needing improvement: ";
          foreach ($bottomSOs as $soId => $data) {
              $bottomSOsText .= sprintf("%s (%.1f%%), ", $soId, $data['averageScore']);
          }
          $insights['summary'][] = rtrim($bottomSOsText, ", ");
      }

      // Generate recommendations
      if ($categoryCounts['No Target'] > 0) {
          $insights['recommendations'][] = [
              'type' => 'target_setting',
              'priority' => 'high',
              'text' => sprintf(
                  "Set targets for the %d indicators currently without targets to enable proper performance tracking.",
                  $categoryCounts['No Target']
              )
          ];
      }

      if ($categoryCounts['No Data'] > 0) {
          $insights['recommendations'][] = [
              'type' => 'data_collection',
              'priority' => 'high',
              'text' => sprintf(
                  "Improve data collection for the %d indicators without reported data.",
                  $categoryCounts['No Data']
              )
          ];
      }

      if ($categoryCounts['Not Performing'] > 0) {
          $insights['recommendations'][] = [
              'type' => 'intervention',
              'priority' => 'high',
              'text' => sprintf(
                  "Develop intervention strategies for the %d underperforming indicators (below 10%% achievement).",
                  $categoryCounts['Not Performing']
              )
          ];
      }

      if ($categoryCounts['In Progress'] > 0) {
          $insights['recommendations'][] = [
              'type' => 'acceleration',
              'priority' => 'medium',
              'text' => sprintf(
                  "Accelerate implementation for the %d indicators in progress (10-50%% achievement).",
                  $categoryCounts['In Progress']
              )
          ];
      }

      // Add cluster-specific recommendations
      $insights['recommendations'][] = [
          'type' => 'cluster_specific',
          'priority' => 'medium',
          'text' => sprintf(
              "For %s cluster, focus on improving data quality and completeness across all strategic objectives.",
              $cluster->Cluster_Name
          )
      ];

      // Add timeline-specific insights
      if (count($timelines) === 1) {
          $timeline = $timelines[0];
          $insights['trends'][] = [
              'type' => 'timeline_info',
              'text' => sprintf(
                  "This report covers %s (%s) for the year %d.",
                  $timeline->ReportName,
                  $timeline->Type,
                  $timeline->Year
              )
          ];

          // Add status-specific insights
          if (isset($timeline->status) && $timeline->status === 'In Progress') {
              $insights['trends'][] = [
                  'type' => 'timeline_status',
                  'text' => "This reporting period is still in progress. Final results may change as more data is collected."
              ];
          } else if (isset($timeline->status) && $timeline->status === 'Completed') {
              $insights['trends'][] = [
                  'type' => 'timeline_status',
                  'text' => "This reporting period is complete. All data has been finalized."
              ];
          }

          // Add closing date information if available
          if (isset($timeline->ClosingDate) && !empty($timeline->ClosingDate)) {
              $insights['trends'][] = [
                  'type' => 'timeline_closing_date',
                  'text' => sprintf(
                      "This reporting period closed on %s.",
                      date('F j, Y', strtotime($timeline->ClosingDate))
                  )
              ];
          }
      } else {
          $insights['trends'][] = [
              'type' => 'timeline_aggregation',
              'text' => sprintf(
                  "This report aggregates data from %d reporting periods.",
                  count($timelines)
              )
          ];

          // Group timelines by type
          $timelinesByType = [];
          foreach ($timelines as $timeline) {
              if (!isset($timelinesByType[$timeline->Type])) {
                  $timelinesByType[$timeline->Type] = [];
              }
              $timelinesByType[$timeline->Type][] = $timeline;
          }

          foreach ($timelinesByType as $type => $typeTimelines) {
              $insights['trends'][] = [
                  'type' => 'timeline_type',
                  'text' => sprintf(
                      "Includes %d %s for the year %d.",
                      count($typeTimelines),
                      $type,
                      $typeTimelines[0]->Year
                  )
              ];
          }

          // Add information about closing dates
          $closedTimelines = array_filter($timelines, function($timeline) {
              return isset($timeline->ClosingDate) && !empty($timeline->ClosingDate);
          });

          if (count($closedTimelines) > 0) {
              // Sort by closing date
              usort($closedTimelines, function($a, $b) {
                  return strtotime($b->ClosingDate) - strtotime($a->ClosingDate);
              });

              $insights['trends'][] = [
                  'type' => 'timeline_closing_dates',
                  'text' => sprintf(
                      "The most recent data is from %s, which closed on %s.",
                      $closedTimelines[0]->ReportName,
                      date('F j, Y', strtotime($closedTimelines[0]->ClosingDate))
                  )
              ];
          }
      }

      // Detect anomalies and patterns
      $anomalies = $this->detectAnomalies($performanceResults);
      $insights['anomalies'] = $anomalies;

      return $insights;
  }

  /**
   * Detect anomalies and patterns in performance data
   */
  private function detectAnomalies($performanceResults)
  {
      $anomalies = [];

      // Group results by strategic objective
      $resultsBySO = [];
      foreach ($performanceResults as $result) {
          $soId = $result['indicator']['SO_ID'];
          if (!isset($resultsBySO[$soId])) {
              $resultsBySO[$soId] = [];
          }
          $resultsBySO[$soId][] = $result;
      }

      // Check for strategic objectives with inconsistent performance
      foreach ($resultsBySO as $soId => $soResults) {
          $categories = [];
          foreach ($soResults as $result) {
              $category = $result['performanceCategory'];
              if (!isset($categories[$category])) {
                  $categories[$category] = 0;
              }
              $categories[$category]++;
          }

          // If an SO has both high and low performing indicators, flag it
          if (
              (isset($categories['Met']) || isset($categories['On Track'])) &&
              (isset($categories['Not Performing']) || isset($categories['In Progress']))
          ) {
              $anomalies[] = [
                  'type' => 'inconsistent_performance',
                  'severity' => 'warning',
                  'strategic_objective' => $soId,
                  'description' => sprintf(
                      "Strategic Objective %s shows inconsistent performance across indicators.",
                      $soId
                  ),
                  'details' => $categories
              ];
          }
      }

      // Check for indicators with unusually high or low performance
      $scores = [];
      foreach ($performanceResults as $result) {
          if ($result['score'] !== null) {
              $scores[] = $result['score'];
          }
      }

      if (!empty($scores)) {
          $avgScore = array_sum($scores) / count($scores);
          $stdDev = $this->calculateStandardDeviation($scores);

          foreach ($performanceResults as $result) {
              if ($result['score'] !== null) {
                  // Flag indicators that are more than 2 standard deviations from the mean
                  if (abs($result['score'] - $avgScore) > 2 * $stdDev) {
                      $anomalies[] = [
                          'type' => 'statistical_outlier',
                          'severity' => 'info',
                          'indicator' => $result['indicator'],
                          'description' => sprintf(
                              "Indicator %s (%s) has an unusual performance score (%.1f%%) compared to the average (%.1f%%).",
                              $result['indicator']['Indicator_Number'],
                              $result['indicator']['Indicator_Name'],
                              $result['score'],
                              $avgScore
                          ),
                          'details' => [
                              'score' => $result['score'],
                              'average' => $avgScore,
                              'standardDeviation' => $stdDev
                          ]
                      ];
                  }
              }
          }
      }

      // Check for aggregation anomalies in annual reports
      foreach ($performanceResults as $result) {
          if (isset($result['detailedAnalysis']['aggregationDetails'])) {
              $aggregationDetails = $result['detailedAnalysis']['aggregationDetails'];

              // Check for mixed timeline types in aggregation
              if (isset($aggregationDetails['timelineTypes']) && count($aggregationDetails['timelineTypes']) > 1) {
                  $anomalies[] = [
                      'type' => 'mixed_aggregation',
                      'severity' => 'info',
                      'indicator' => $result['indicator'],
                      'description' => sprintf(
                          "Indicator %s (%s) aggregates data from multiple timeline types: %s.",
                          $result['indicator']['Indicator_Number'],
                          $result['indicator']['Indicator_Name'],
                          implode(', ', $aggregationDetails['timelineTypes'])
                      ),
                      'details' => $aggregationDetails
                  ];
              }

              // Check for significant differences between sources in aggregation
              if (isset($aggregationDetails['sources']) && count($aggregationDetails['sources']) > 1) {
                  $sources = $aggregationDetails['sources'];
                  $values = [];

                  foreach ($sources as $source) {
                      if (isset($source['value'])) {
                          $values[] = $source['value'];
                      } else if (isset($source['total']) && isset($source['count']) && $source['count'] > 0) {
                          $values[] = $source['total'] / $source['count'];
                      }
                  }

                  if (count($values) > 1) {
                      $maxValue = max($values);
                      $minValue = min($values);

                      // If max is more than 2x the min, flag it
                      if ($minValue > 0 && $maxValue > $minValue * 2) {
                          $anomalies[] = [
                              'type' => 'inconsistent_sources',
                              'severity' => 'warning',
                              'indicator' => $result['indicator'],
                              'description' => sprintf(
                                  "Indicator %s (%s) shows significant variation between different reporting periods.",
                                  $result['indicator']['Indicator_Number'],
                                  $result['indicator']['Indicator_Name']
                              ),
                              'details' => [
                                  'sources' => $sources,
                                  'maxValue' => $maxValue,
                                  'minValue' => $minValue,
                                  'ratio' => $maxValue / $minValue
                              ]
                          ];
                      }
                  }
              }
          }
      }

      return $anomalies;
  }

  /**
   * Calculate standard deviation of an array of values
   */
  private function calculateStandardDeviation($values)
  {
      $count = count($values);
      if ($count < 2) {
          return 0;
      }

      $mean = array_sum($values) / $count;
      $variance = 0;

      foreach ($values as $value) {
          $variance += pow($value - $mean, 2);
      }

      return sqrt($variance / $count);
  }

  /**
   * Get performance category for a score
   */
  private function getCategoryForScore($score)
  {
      if ($score < 10) {
          return 'Not Performing';
      } elseif ($score >= 10 && $score < 50) {
          return 'In Progress';
      } elseif ($score >= 50 && $score < 90) {
          return 'On Track';
      } else {
          return 'Met';
      }
  }

  /**
   * Filter performance data based on user input
   */
  public function filterReport(Request $request)
  {
      // Check authorization
      if ($redirect = $this->checkEcsaHcAuthorization()) {
          return $redirect;
      }

      // Get filter parameters
      $clusterId = $request->input('cluster_id');
      $timelineId = $request->input('timeline_id');
      $soId = $request->input('strategic_objective');
      $performanceCategory = $request->input('performance_category');
      $reportYear = $request->input('report_year');
      $timelineType = $request->input('timeline_type');

      // Redirect to generate report with filters
      return $this->generateReport($request);
  }

  /**
   * Export performance report to Excel/PDF
   */
  public function exportReport(Request $request)
  {
      // Check authorization
      if ($redirect = $this->checkEcsaHcAuthorization()) {
          return $redirect;
      }

      // Implementation for exporting report
      // This would typically use a library like PhpSpreadsheet or TCPDF

      return redirect()->back()->with('info', 'Export functionality is not implemented in this version.');
  }
}