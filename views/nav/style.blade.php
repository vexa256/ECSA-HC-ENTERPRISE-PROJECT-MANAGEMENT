<style>
    /* iOS-inspired animations and effects */
    @keyframes subtle-glow {
        0% {
            box-shadow: 0 0 5px rgba(136, 136, 136, 0.1);
        }

        50% {
            box-shadow: 0 0 10px rgba(136, 136, 136, 0.2);
        }

        100% {
            box-shadow: 0 0 5px rgba(136, 136, 136, 0.1);
        }
    }

    .menu-item {
        transition: all 0.3s ease;
        border: 1px solid rgba(209, 213, 219, 0.3);
        animation: subtle-glow 3s infinite;
    }

    .menu-item:hover {
        transform: translateY(-2px);
        border-color: rgba(136, 136, 136, 0.5);
        box-shadow: 0 4px 12px rgba(136, 136, 136, 0.1);
    }

    .menu-icon {
        transition: all 0.3s ease;
    }

    .menu-item:hover .menu-icon {
        transform: scale(1.1);
    }

    /* Slide panel styles with iOS-like blur effect */
    .slide-panel {
        position: fixed;
        top: 0;
        left: 4rem;
        height: 100vh;
        width: 0;
        overflow: hidden;
        background-color: transparent;
        transition: width 0.3s ease;
        z-index: 30;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .slide-panel.active {
        width: 320px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }

    .slide-panel-content {
        width: 320px;
        height: 100%;
        padding: 1.5rem;
        overflow-y: auto;
    }

    /* iOS-style scrollbar */
    .slide-panel-content::-webkit-scrollbar {
        width: 8px;
    }

    .slide-panel-content::-webkit-scrollbar-track {
        background: transparent;
    }

    .slide-panel-content::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.1);
        border-radius: 20px;
    }
</style>
