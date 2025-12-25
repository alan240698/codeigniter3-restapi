const ValidationRulesSLA = {
    slaPolicy: {
        name: {
            required: true,
            minLength: 3,
            maxLength: 100,
            pattern: /^[a-zA-Z0-9\s\-_]+$/,
            message: {
                required: 'SLA Policy name is required',
                minLength: 'Name must be at least 3 characters',
                maxLength: 'Name must not exceed 100 characters',
                pattern: 'Name can only contain letters, numbers, spaces, hyphens and underscores'
            }
        },
        priority: {
            required: true,
            message: {
                required: 'Please select a priority level'
            }
        },
        resolution_hours: {
            required: true,
            min: 1,
            max: 720,
            message: {
                required: 'Resolution time is required',
                min: 'Resolution time must be at least 1 hour',
                max: 'Resolution time must not exceed 720 hours (30 days)'
            }
        },
        first_response_hours: {
            required: false,
            min: 1,
            max: 168,
            message: {
                min: 'First response time must be at least 1 hour',
                max: 'First response time must not exceed 168 hours (7 days)'
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
        }
    },
    slaMapping: {
        it_service_id: {
            required: true,
            message: {
                required: 'Please select an IT Service'
            }
        },
        sla_policy_id: {
            required: true,
            message: {
                required: 'Please select an SLA Policy'
            }
        }
    }
};
