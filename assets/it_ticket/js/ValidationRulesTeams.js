const ValidationRulesTeams = {
    team: {
        name: {
            required: true,
            minLength: 3,
            maxLength: 100,
            pattern: /^[a-zA-Z0-9\s\-_]+$/,
            message: {
                required: 'Team name is required',
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
                required: 'Team code is required',
                minLength: 'Code must be at least 2 characters',
                maxLength: 'Code must not exceed 50 characters',
                pattern: 'Code must be uppercase letters, numbers and underscores only'
            }
        },

        support_level: {
            required: true,
            allowedValues: ['L1', 'L2', 'L3', 'L4'],
            message: {
                required: 'Support level is required',
                allowedValues: 'Support level must be one of L1, L2, L3, L4'
            }
        },

        department_code: {
            required: true,
            maxLength: 20,
            pattern: /^[A-Z0-9_]+$/,
            message: {
                required: 'Department code is required',
                maxLength: 'Department code must not exceed 20 characters',
                pattern: 'Department code can contain uppercase letters, numbers and underscores only'
            }
        },

        office_id: {
            required: true,
            numeric: true,
            message: {
                required: 'Office is required',
                numeric: 'Office must be a valid number'
            }
        },

        country: {
            required: true,
            maxLength: 50,
            message: {
                required: 'Country is required',
                maxLength: 'Country must not exceed 50 characters'
            }
        },

        manager_id: {
            required: true,
            numeric: true,
            message: {
                required: 'Manager is required',
                numeric: 'Manager must be a valid ID'
            }
        },

        email: {
            required: false,
            maxLength: 100,
            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            message: {
                maxLength: 'Email must not exceed 100 characters',
                pattern: 'Email format is invalid'
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

        status: {
            required: true,
            allowedValues: ['active', 'inactive'],
            message: {
                required: 'Status is required',
                allowedValues: 'Status must be active or inactive'
            }
        }
    },
    teamMember: {
        team_id: {
            required: true,
            message: {
                required: 'Please select a team'
            }
        },
        user_id: {
            required: true,
            message: {
                required: 'Please select a user'
            }
        },
        role: {
            required: true,
            message: {
                required: 'Please select a role'
            }
        }
    }
};
