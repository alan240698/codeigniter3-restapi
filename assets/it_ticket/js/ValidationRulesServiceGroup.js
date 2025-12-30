const ValidationRulesServiceGroup = {
    serviceGroup: {
        name: {
            required: true,
            minLength: 3,
            maxLength: 100,
            pattern: /^[a-zA-Z0-9\s\-_]+$/,
            message: {
                required: 'Service group name is required',
                minLength: 'Name must be at least 3 characters',
                maxLength: 'Name must not exceed 100 characters',
                pattern: 'Name can only contain letters, numbers, spaces, hyphens and underscores'
            }
        },
        code: {
            required: true,
            minLength: 2,
            maxLength: 20,
            pattern: /^[A-Z0-9_]+$/,
            message: {
                required: 'Service group code is required',
                minLength: 'Code must be at least 2 characters',
                maxLength: 'Code must not exceed 20 characters',
                pattern: 'Code must be uppercase letters, numbers and underscores only'
            }
        },
        icon: {
            required: false,
            maxLength: 2,
            message: {
                maxLength: 'Icon must be 1-2 characters (emoji or symbol)'
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
    ticketType: {
        name: {
            required: true,
            minLength: 3,
            maxLength: 100,
            pattern: /^[a-zA-Z0-9\s\-_]+$/,
            message: {
                required: 'Ticket type name is required',
                minLength: 'Name must be at least 3 characters',
                maxLength: 'Name must not exceed 100 characters',
                pattern: 'Name can only contain letters, numbers, spaces, hyphens and underscores'
            }
        },
        code: {
            required: true,
            minLength: 2,
            maxLength: 30,
            pattern: /^[A-Z0-9_]+$/,
            message: {
                required: 'Ticket type code is required',
                minLength: 'Code must be at least 2 characters',
                maxLength: 'Code must not exceed 30 characters',
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
        }
    },
    itService: {
        service_group_id: {
            required: true,
            message: {
                required: 'Please select a service group'
            }
        },
        name: {
            required: true,
            minLength: 3,
            maxLength: 100,
            pattern: /^[a-zA-Z0-9\s\-_:,.]+$/,
            message: {
                required: 'It ticket name is required',
                minLength: 'Name must be at least 3 characters',
                maxLength: 'Name must not exceed 100 characters',
                pattern: 'Name can only contain letters, numbers, spaces, hyphens and underscores'
            }
        },
        code: {
            required: true,
            minLength: 2,
            maxLength: 40,
            pattern: /^[A-Z0-9_]+$/,
            message: {
                required: 'It ticket code is required',
                minLength: 'Code must be at least 2 characters',
                maxLength: 'Code must not exceed 40 characters',
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
        input_type: {
            required: true,
            message: {
                required: 'Please select input type'
            }
        },
    }
};