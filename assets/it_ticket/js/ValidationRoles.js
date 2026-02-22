const ValidationRoles = {
    roles: {
        name: {
            required: true,
            minLength: 3,
            maxLength: 50,
            pattern: /^[A-Z0-9_]+$/,
            message: {
                required: 'Role name is required',
                minLength: 'Role name must be at least 3 characters',
                maxLength: 'Role name must not exceed 50 characters',
                pattern: 'Role name must be uppercase letters, numbers and underscores only'
            }
        },

        display_name: {
            required: true,
            minLength: 3,
            maxLength: 100,
            message: {
                required: 'Display name is required',
                minLength: 'Display name must be at least 3 characters',
                maxLength: 'Display name must not exceed 100 characters'
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
    },
};
