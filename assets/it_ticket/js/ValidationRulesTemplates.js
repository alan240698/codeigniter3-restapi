const ValidationRulesTemplates = {
    template: {
        template_name: {
            required: true,
            minLength: 3,
            maxLength: 255,
            pattern: /^[a-zA-Z0-9\s\-_]+$/,
            message: {
                required: 'Template name is required',
                minLength: 'Name must be at least 3 characters',
                maxLength: 'Name must not exceed 255 characters',
                pattern: 'Name can only contain letters, numbers, spaces, hyphens and underscores'
            }
        },
        template_code: {
            required: true,
            minLength: 2,
            maxLength: 100,
            pattern: /^[A-Z0-9_]+$/,
            message: {
                required: 'Template code is required',
                minLength: 'Code must be at least 2 characters',
                maxLength: 'Code must not exceed 100 characters',
                pattern: 'Code must be uppercase letters, numbers and underscores only'
            }
        },
        subject: {
            required: true,
            minLength: 5,
            maxLength: 500,
            message: {
                required: 'Email subject is required',
                minLength: 'Subject must be at least 5 characters',
                maxLength: 'Subject must not exceed 500 characters'
            }
        },
        body: {
            required: true,
            minLength: 10,
            message: {
                required: 'Email body is required',
                minLength: 'Body must be at least 10 characters'
            }
        },
        variables: {
            required: false,
            custom: (value) => {
                if (!value || value.trim() === '') return true;
                try {
                    const parsed = JSON.parse(value);
                    return Array.isArray(parsed);
                } catch (e) {
                    return false;
                }
            },
            message: {
                custom: 'Variables must be a valid JSON array (e.g., ["var1", "var2"])'
            }
        }
    }
};
