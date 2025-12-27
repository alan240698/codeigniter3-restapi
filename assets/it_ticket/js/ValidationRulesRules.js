const ValidationRulesRules = {
    routingRule: {
        it_service_id: {
            required: true,
            numeric: true,
            message: {
                required: 'IT Service is required',
                numeric: 'IT Service must be a valid ID'
            }
        },

        rule_name: {
            required: true,
            minLength: 3,
            maxLength: 100,
            message: {
                required: 'Rule name is required',
                minLength: 'Rule name must be at least 3 characters',
                maxLength: 'Rule name must not exceed 100 characters'
            }
        },

        priority: {
            required: false,
            numeric: true,
            min: 1,
            message: {
                numeric: 'Priority must be a number',
                min: 'Priority must be greater than 0'
            }
        },

        assignment_type: {
            required: true,
            allowedValues: ['team', 'user'],
            message: {
                required: 'Assignment type is required',
                allowedValues: 'Assignment type must be team or user'
            }
        },

        target_team_id: {
            required: false,
            numeric: true,
            message: {
                numeric: 'Target team must be a valid ID'
            }
        },

        target_user_id: {
            required: false,
            numeric: true,
            message: {
                numeric: 'Target user must be a valid ID'
            }
        },

        conditions: {
            required: false,
            json: true,
            message: {
                json: 'Conditions must be valid JSON'
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
    approvalRule: {
        it_service_id: {
            required: true,
            numeric: true,
            message: {
                required: 'IT Service is required',
                numeric: 'IT Service must be a valid ID'
            }
        },

        rule_name: {
            required: true,
            minLength: 3,
            maxLength: 100,
            message: {
                required: 'Rule name is required',
                minLength: 'Rule name must be at least 3 characters',
                maxLength: 'Rule name must not exceed 100 characters'
            }
        },

        approval_type: {
            required: true,
            allowedValues: ['manager', 'specific_user'],
            message: {
                required: 'Approval type is required',
                allowedValues: 'Approval type must be manager or specific_user'
            }
        },

        approver_user_id: {
            required: false,
            numeric: true,
            message: {
                numeric: 'Approver must be a valid ID'
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
    levelRule: {
        it_service_id: {
            required: true,
            numeric: true,
            message: {
                required: 'IT Service is required',
                numeric: 'IT Service must be a valid ID'
            }
        },

        level_number: {
            required: true,
            numeric: true,
            min: 1,
            max: 10,
            message: {
                required: 'Level number is required',
                numeric: 'Level number must be a number',
                min: 'Level number must be at least 1',
                max: 'Level number must not exceed 10'
            }
        },

        level_name: {
            required: true,
            minLength: 3,
            maxLength: 50,
            message: {
                required: 'Level name is required',
                minLength: 'Level name must be at least 3 characters',
                maxLength: 'Level name must not exceed 50 characters'
            }
        },

        support_team_id: {
            required: false,
            numeric: true,
            message: {
                numeric: 'Support team must be a valid ID'
            }
        },

        auto_escalate_hours: {
            required: false,
            numeric: true,
            min: 1,
            message: {
                numeric: 'Auto escalate hours must be a number',
                min: 'Auto escalate hours must be at least 1'
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
    customField: {
        it_service_id: {
            required: true,
            numeric: true,
            message: {
                required: 'IT Service is required',
                numeric: 'IT Service must be a valid ID'
            }
        },

        field_name: {
            required: true,
            minLength: 2,
            maxLength: 50,
            pattern: /^[a-z_]+$/,
            message: {
                required: 'Field name is required',
                minLength: 'Field name must be at least 2 characters',
                maxLength: 'Field name must not exceed 50 characters',
                pattern: 'Field name must contain only lowercase letters and underscores'
            }
        },

        field_label: {
            required: true,
            minLength: 2,
            maxLength: 100,
            message: {
                required: 'Field label is required',
                minLength: 'Field label must be at least 2 characters',
                maxLength: 'Field label must not exceed 100 characters'
            }
        },

        field_type: {
            required: true,
            allowedValues: ['text', 'textarea', 'number', 'date', 'select', 'radio', 'checkbox', 'file', 'user_select'],
            message: {
                required: 'Field type is required',
                allowedValues: 'Invalid field type selected'
            }
        },

        field_options: {
            required: false,
            json: true,
            message: {
                json: 'Field options must be valid JSON'
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
    }
};
