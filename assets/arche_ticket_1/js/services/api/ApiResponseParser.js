import Logger from '../../utils/logger.js';

class ApiResponseParser {
    async parse(response) {
        const responseText = await response.text();

        if (!responseText.trim()) {
            Logger.warn('Empty response received');
            return { success: false, message: 'Empty response from server' };
        }

        if (this._isHtmlResponse(responseText)) {
            Logger.error('HTML Error Response:', responseText.substring(0, 500));
            throw new Error('Server returned HTML error. Check server logs.');
        }

        return this._parseJson(responseText);
    }

    _isHtmlResponse(text) {
        const trimmed = text.trim();
        return trimmed.startsWith('<') || trimmed.startsWith('<!DOCTYPE');
    }

    _parseJson(text) {
        try {
            return JSON.parse(text);
        } catch (parseError) {
            Logger.error('JSON parse error:', parseError);
            Logger.error('Raw response:', text.substring(0, 500));
            throw new Error('Invalid JSON response from server');
        }
    }
}

export default ApiResponseParser;