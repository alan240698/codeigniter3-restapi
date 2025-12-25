const ValidationRulesRules = {
    rule: {
        name: {
            required: true,
            minLength: 3,
            maxLength: 100,
            pattern: /^[a-zA-Z0-9\s\-_]+$/,
            message: {
                required: 'Rule name is required',
                minLength: 'Name must be at least 3 characters',
                maxLength: 'Name must not exceed 100 characters',
                pattern: 'Name can only contain letters, numbers, spaces, hyphens and underscores'
            }
        },
        code: {
            required: true,
            minLength: 2,
            maxLength: 50,
            pattern: /^[A-Z0-9_]+$/,
            message: {
                required: 'Rule code is required',
                minLength: 'Code must be at least 2 characters',
                maxLength: 'Code must not exceed 50 characters',
                pattern: 'Code must be uppercase letters, numbers and underscores only'
            }
        },
        description: {
            required: false,
            minLength: 10,
            maxLength: 500,
            message: {
                minLength: 'Description must be at least 10 characters',
                maxLength: 'Description must not exceed 500 characters'
            }
        },
        priority: {
            required: true,
            min: 1,
            max: 100,
            message: {
                required: 'Priority is required',
                min: 'Priority must be at least 1',
                max: 'Priority must not exceed 100'
            }
        }
    }
};
