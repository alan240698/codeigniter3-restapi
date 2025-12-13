class UploadInteractionHandler {
    constructor(eventManager) {
        this.eventManager = eventManager;
    }

    setupClickTrigger(wrapper, input) {
        const clickHandler = (e) => {
            if (this._shouldTriggerInput(e, wrapper)) {
                input.click();
            }
        };
        
        this.eventManager.add(wrapper, 'click', clickHandler);
    }

    _shouldTriggerInput(e, wrapper) {
        return (
            e.target === wrapper || 
            e.target.closest('.file-upload-label') ||
            e.target.classList.contains('file-upload-label')
        );
    }
}

export default UploadInteractionHandler;