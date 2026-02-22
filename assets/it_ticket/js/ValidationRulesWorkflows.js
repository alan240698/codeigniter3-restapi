const ValidationRulesWorkflows = {
    workflows: {
        name: {
            required: true,
            minLength: 3,
            maxLength: 100,
            pattern: /^[a-zA-Z0-9\s\-_]+$/,
            message: {
                required: 'Workflow name is required',
                minLength: 'Workflow name must be at least 3 characters',
                maxLength: 'Workflow name must not exceed 100 characters',
                pattern: 'Name can only contain letters, numbers, spaces, hyphens and underscores'
            }
        },
        code: {
            required: true,
            minLength: 2,
            maxLength: 50,
            pattern: /^[A-Z0-9_]+$/,
            message: {
                required: 'Workflow code is required',
                minLength: 'Workflow code must be at least 2 characters',
                maxLength: 'Workflow code must not exceed 50 characters',
                pattern: 'Workflow code must be uppercase letters, numbers and underscores only'
            }
        },
        description: {
            required: false,
            minLength: 10,
            maxLength: 500,
            message: {
                minLength: 'Workflow description must be at least 10 characters',
                maxLength: 'Workflow description must not exceed 500 characters'
            }
        }
    },
    states: {
         name: {
            required: true,
            minLength: 3,
            maxLength: 50,
            pattern: /^[a-zA-Z0-9\s\-_]+$/,
            message: {
                required: 'State name is required',
                minLength: 'State name must be at least 3 characters',
                maxLength: 'State name must not exceed 50 characters',
                pattern: 'Name can only contain letters, numbers, spaces, hyphens and underscores'
            }
        },
        code: {
            required: true,
            minLength: 2,
            maxLength: 50,
            pattern: /^[A-Z0-9_]+$/,
            message: {
                required: 'State code is required',
                minLength: 'State code must be at least 2 characters',
                maxLength: 'State code must not exceed 50 characters',
                pattern: 'State code must be uppercase letters, numbers and underscores only'
            }
        },
        description: {
            required: false,
            minLength: 10,
            maxLength: 500,
            message: {
                minLength: 'State description must be at least 10 characters',
                maxLength: 'State description must not exceed 500 characters'
            }
        },
        state_type: {
            required: true,
            allowedValues: ['initial','intermediate','final','cancelled'],
            message: {
                required: 'State type is required',
                allowedValues: 'Invalid state type'
            }
        },
        color: {
            required: true,
            pattern: /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/,
            message: {
                required: 'Color is required',
                pattern: 'Color must be a valid hex code (e.g. #1dfd1e)'
            }
        },
        sla_hours: {
            required: false,
            numeric: true,
            min: 0,
            max: 10000,
            message: {
                required: 'SLA hours is required',
                numeric: 'SLA hours must be a number',
                min: 'SLA hours must be greater than or equal to 0',
                max: 'SLA hours must not exceed 10000'
            }
        },
    },
    transitions: {
         name: {
            required: true,
            minLength: 3,
            maxLength: 50,
            pattern: /^[a-zA-Z0-9\s\-_]+$/,
            message: {
                required: 'Trans name is required',
                minLength: 'Trans name must be at least 3 characters',
                maxLength: 'Trans name must not exceed 50 characters',
                pattern: 'Trans Name can only contain letters, numbers, spaces, hyphens and underscores'
            }
        },
        from_state_id: {
            required: true,
            message: {
                required: 'From state is required',
            }
        },
        to_state_id: {
            required: true,
            message: {
                required: 'To state is required',
            }
        },
        required_role: {
            required: true,
            message: {
                required: 'Select role is required',
            }
        },
        conditions: {
            required: true,
            message: {
                required: 'Please enter conditions',
            }
        },
        description: {
            required: false,
            minLength: 10,
            maxLength: 500,
            message: {
                minLength: 'Workflow description must be at least 10 characters',
                maxLength: 'Workflow description must not exceed 500 characters'
            }
        },
    }
};