class StorageStatsCalculator {
    constructor(fileStorageManager) {
        this.fileStorage = fileStorageManager;
    }

    getStats() {
        const totalSize = this.fileStorage.getTotalSize();
        const formCount = this.fileStorage.fileDataMap.size;
        const totalFiles = this._countTotalFiles();
        const maxSize = this.fileStorage.maxStorageSize;

        return {
            formCount,
            totalFiles,
            totalSize,
            totalSizeFormatted: this._formatBytes(totalSize),
            maxSize,
            maxSizeFormatted: this._formatBytes(maxSize),
            usagePercent: Math.round((totalSize / maxSize) * 100)
        };
    }

    _countTotalFiles() {
        let total = 0;
        for (const files of this.fileStorage.fileDataMap.values()) {
            total += files.length;
        }
        return total;
    }

    _formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
    }
}

export default StorageStatsCalculator;