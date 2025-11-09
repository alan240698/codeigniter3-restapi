/**
 * File Manager Module
 * Main entry point for file management operations
 */
import FileManager from './FileManager.js';
import FileValidator from './FileValidator.js';
import FileRenderer from './FileRenderer.js';
import FileRemover from './FileRemover.js';

// Export default instance (singleton pattern)
export default FileManager;

// Export classes for testing or custom instances
export {
    FileValidator,
    FileRenderer,
    FileRemover
};