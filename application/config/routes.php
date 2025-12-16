<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/*
|--------------------------------------------------------------------------
| SUPER ADMIN ROUTES
|--------------------------------------------------------------------------
*/

// Dashboard index page - MVC
$route['it-ticket']                                     = 'it_ticket/admin/DashboardController/index';
// $route['it-ticket/dashboard']                           = 'it_ticket/admin/DashBoardController/dashBoardView';

/*
|--------------------------------------------------------------------------  
| IT TICKET - SERVICE GROUPS API (REST)
|--------------------------------------------------------------------------  
*/

$route['service-groups']                    = 'api/it_ticket/ServiceGroupController/index';
$route['service-groups/show/(:num)']        = 'api/it_ticket/ServiceGroupController/show/$1';
$route['service-groups/store']              = 'api/it_ticket/ServiceGroupController/store';
$route['service-groups/update/(:num)']      = 'api/it_ticket/ServiceGroupController/update/$1';
$route['service-groups/delete/(:num)']      = 'api/it_ticket/ServiceGroupController/delete/$1';

/*
|--------------------------------------------------------------------------  
| IT TICKET - TICKET TYPES API (REST)
|--------------------------------------------------------------------------  
*/

$route['ticket-types']                  = 'api/it_ticket/TicketTypeController/index';
$route['ticket-types/show/(:num)']      = 'api/it_ticket/TicketTypeController/show/$1';
$route['ticket-types/store']            = 'api/it_ticket/TicketTypeController/store';
$route['ticket-types/all-group']        = 'api/it_ticket/TicketTypeController/get_all';
$route['ticket-types/update/(:num)']    = 'api/it_ticket/TicketTypeController/update/$1';
$route['ticket-types/delete/(:num)']    = 'api/it_ticket/TicketTypeController/delete/$1';

/*
|--------------------------------------------------------------------------  
| IT TICKET - ISSUE TYPES API (REST)
|--------------------------------------------------------------------------  
*/

$route['issue-types']                   = 'api/it_ticket/IssueTypeController/index';
$route['issue-types/show/(:num)']       = 'api/it_ticket/IssueTypeController/show/$1';
$route['issue-types/store']             = 'api/it_ticket/IssueTypeController/store';
$route['issue-types/all-group-type']    = 'api/it_ticket/IssueTypeController/get_all';
$route['issue-types/update/(:num)']     = 'api/it_ticket/IssueTypeController/update/$1';
$route['issue-types/delete/(:num)']     = 'api/it_ticket/IssueTypeController/delete/$1';

/*
|--------------------------------------------------------------------------  
| ADDITIONAL HELPER ROUTES
|--------------------------------------------------------------------------  
*/

$route['api/service-groups/all']         = 'api/it_ticket/ServiceGroupController/get_all';

/*
|--------------------------------------------------------------------------  
| IT TICKET - WORKFLOWS API (REST)
|--------------------------------------------------------------------------  
*/

// WORKFLOWS
$route['workflows']                         = 'api/it_ticket/WorkflowController/index';
$route['workflows/show/(:num)']             = 'api/it_ticket/WorkflowController/show/$1';
$route['workflows/store']                   = 'api/it_ticket/WorkflowController/store';
$route['workflows/all-group-type']          = 'api/it_ticket/WorkflowController/get_all';
$route['workflows/all']                     = 'api/it_ticket/WorkflowController/get_all';
$route['workflows/update/(:num)']           = 'api/it_ticket/WorkflowController/update/$1';
$route['workflows/delete/(:num)']           = 'api/it_ticket/WorkflowController/delete/$1';

// WORKFLOW STATES
$route['workflow-states']                   = 'api/it_ticket/WorkflowStateController/index';
$route['workflow-states/show/(:num)']       = 'api/it_ticket/WorkflowStateController/show/$1';
$route['workflow-states/store']             = 'api/it_ticket/WorkflowStateController/store';
$route['workflow-states/update/(:num)']     = 'api/it_ticket/WorkflowStateController/update/$1';
$route['workflow-states/delete/(:num)']     = 'api/it_ticket/WorkflowStateController/delete/$1';

// WORKFLOW TRANSITIONS
$route['workflow-transitions']                  = 'api/it_ticket/WorkflowTransitionController/index';
$route['workflow-transitions/show/(:num)']      = 'api/it_ticket/WorkflowTransitionController/show/$1';
$route['workflow-transitions/store']            = 'api/it_ticket/WorkflowTransitionController/store';
$route['workflow-transitions/update/(:num)']    = 'api/it_ticket/WorkflowTransitionController/update/$1';
$route['workflow-transitions/delete/(:num)']    = 'api/it_ticket/WorkflowTransitionController/delete/$1';

// ISSUE TYPE WORKFLOW MAPPING
$route['issue-type-workflows']                      = 'api/it_ticket/IssueTypeWorkflowController/index';
$route['issue-type-workflows/show/(:num)']          = 'api/it_ticket/IssueTypeWorkflowController/show/$1';
$route['issue-type-workflows/store']                = 'api/it_ticket/IssueTypeWorkflowController/store';
$route['issue-type-workflows/update/(:num)']        = 'api/it_ticket/IssueTypeWorkflowController/update/$1';
$route['issue-type-workflows/delete/(:num)']        = 'api/it_ticket/IssueTypeWorkflowController/delete/$1';
$route['issue-type-workflows/by-issue-type/(:num)'] = 'api/it_ticket/IssueTypeWorkflowController/get_by_issue_type/$1';
$route['issue-type-workflows/stats']                = 'api/it_ticket/IssueTypeWorkflowController/stats';


/*
|--------------------------------------------------------------------------
| IT TICKET - SUPPORT TEAMS & MEMBERS API (REST)
|--------------------------------------------------------------------------
*/

// SUPPORT TEAMS
$route['support-teams']                         = 'api/it_ticket/SupportTeamController/index';
$route['support-teams/show/(:num)']             = 'api/it_ticket/SupportTeamController/show/$1';
$route['support-teams/store']                   = 'api/it_ticket/SupportTeamController/store';
$route['support-teams/update/(:num)']           = 'api/it_ticket/SupportTeamController/update/$1';
$route['support-teams/delete/(:num)']           = 'api/it_ticket/SupportTeamController/delete/$1';
$route['support-teams/all']                     = 'api/it_ticket/SupportTeamController/get_all';

// TEAM MEMBERS
$route['team-members']                          = 'api/it_ticket/TeamMemberController/index';
$route['team-members/show/(:num)']              = 'api/it_ticket/TeamMemberController/show/$1';
$route['team-members/store']                    = 'api/it_ticket/TeamMemberController/store';
$route['team-members/update/(:num)']            = 'api/it_ticket/TeamMemberController/update/$1';
$route['team-members/delete/(:num)']            = 'api/it_ticket/TeamMemberController/delete/$1';
$route['team-members/by-employee/(:num)']       = 'api/it_ticket/TeamMemberController/get_by_employee/$1';


/*
|--------------------------------------------------------------------------
| IT TICKET - RULES MODULE API (REST)
|--------------------------------------------------------------------------
*/

// ISSUE TYPES
$route['issue-types']                           = 'api/it_ticket/IssueTypeController/index';
$route['issue-types/show/(:num)']               = 'api/it_ticket/IssueTypeController/show/$1';
$route['issue-types/store']                     = 'api/it_ticket/IssueTypeController/store';
$route['issue-types/update/(:num)']             = 'api/it_ticket/IssueTypeController/update/$1';
$route['issue-types/delete/(:num)']             = 'api/it_ticket/IssueTypeController/delete/$1';
$route['issue-types/all-group-type']            = 'api/it_ticket/IssueTypeController/get_all_with_groups';
$route['issue-types/by-group/(:num)']           = 'api/it_ticket/IssueTypeController/get_by_group/$1';

// SUPPORT TEAMS
$route['support-teams']                         = 'api/it_ticket/SupportTeamController/index';
$route['support-teams/show/(:num)']             = 'api/it_ticket/SupportTeamController/show/$1';
$route['support-teams/store']                   = 'api/it_ticket/SupportTeamController/store';
$route['support-teams/update/(:num)']           = 'api/it_ticket/SupportTeamController/update/$1';
$route['support-teams/delete/(:num)']           = 'api/it_ticket/SupportTeamController/delete/$1';
$route['support-teams/all']                     = 'api/it_ticket/SupportTeamController/get_all';

// ROUTING RULES
$route['routing-rules']                         = 'api/it_ticket/RoutingRuleController/index';
$route['routing-rules/show/(:num)']             = 'api/it_ticket/RoutingRuleController/show/$1';
$route['routing-rules/store']                   = 'api/it_ticket/RoutingRuleController/store';
$route['routing-rules/update/(:num)']           = 'api/it_ticket/RoutingRuleController/update/$1';
$route['routing-rules/delete/(:num)']           = 'api/it_ticket/RoutingRuleController/delete/$1';

// APPROVAL RULES
$route['approval-rules']                        = 'api/it_ticket/ApprovalRuleController/index';
$route['approval-rules/show/(:num)']            = 'api/it_ticket/ApprovalRuleController/show/$1';
$route['approval-rules/store']                  = 'api/it_ticket/ApprovalRuleController/store';
$route['approval-rules/update/(:num)']          = 'api/it_ticket/ApprovalRuleController/update/$1';
$route['approval-rules/delete/(:num)']          = 'api/it_ticket/ApprovalRuleController/delete/$1';

// LEVEL RULES
$route['level-rules']                           = 'api/it_ticket/LevelRuleController/index';
$route['level-rules/show/(:num)']               = 'api/it_ticket/LevelRuleController/show/$1';
$route['level-rules/store']                     = 'api/it_ticket/LevelRuleController/store';
$route['level-rules/update/(:num)']             = 'api/it_ticket/LevelRuleController/update/$1';
$route['level-rules/delete/(:num)']             = 'api/it_ticket/LevelRuleController/delete/$1';
$route['level-rules/by-issue-type/(:num)']      = 'api/it_ticket/LevelRuleController/get_by_issue_type/$1';

// CUSTOM FIELDS
$route['custom-fields']                         = 'api/it_ticket/CustomFieldController/index';
$route['custom-fields/show/(:num)']             = 'api/it_ticket/CustomFieldController/show/$1';
$route['custom-fields/store']                   = 'api/it_ticket/CustomFieldController/store';
$route['custom-fields/update/(:num)']           = 'api/it_ticket/CustomFieldController/update/$1';
$route['custom-fields/delete/(:num)']           = 'api/it_ticket/CustomFieldController/delete/$1';
$route['custom-fields/by-issue-type/(:num)']    = 'api/it_ticket/CustomFieldController/get_by_issue_type/$1';


/*
|--------------------------------------------------------------------------
| IT TICKET - SLA MODULE API (REST)
|--------------------------------------------------------------------------
*/

// SLA POLICIES
$route['sla-policies']                          = 'api/it_ticket/SLAPolicyController/index';
$route['sla-policies/show/(:num)']              = 'api/it_ticket/SLAPolicyController/show/$1';
$route['sla-policies/store']                    = 'api/it_ticket/SLAPolicyController/store';
$route['sla-policies/update/(:num)']            = 'api/it_ticket/SLAPolicyController/update/$1';
$route['sla-policies/delete/(:num)']            = 'api/it_ticket/SLAPolicyController/delete/$1';
$route['sla-policies/all']                      = 'api/it_ticket/SLAPolicyController/get_all';

// ISSUE TYPE SLA MAPPING
$route['issue-type-sla']                        = 'api/it_ticket/IssueTypeSLAController/index';
$route['issue-type-sla/show/(:num)']            = 'api/it_ticket/IssueTypeSLAController/show/$1';
$route['issue-type-sla/store']                  = 'api/it_ticket/IssueTypeSLAController/store';
$route['issue-type-sla/update/(:num)']          = 'api/it_ticket/IssueTypeSLAController/update/$1';
$route['issue-type-sla/delete/(:num)']          = 'api/it_ticket/IssueTypeSLAController/delete/$1';
$route['issue-type-sla/by-issue-type/(:num)']   = 'api/it_ticket/IssueTypeSLAController/get_by_issue_type/$1';

/*
|--------------------------------------------------------------------------
| IT TICKET - TICKETS MODULE API (REST)
|--------------------------------------------------------------------------
*/

// TICKETS
$route['api/tickets']                           = 'api/it_ticket/TicketController/index';
$route['api/tickets/show/(:num)']               = 'api/it_ticket/TicketController/show/$1';
$route['api/tickets/store']                     = 'api/it_ticket/TicketController/store';
$route['api/tickets/update/(:num)']             = 'api/it_ticket/TicketController/update/$1';
$route['api/tickets/assign/(:num)']             = 'api/it_ticket/TicketController/assign/$1';
$route['api/tickets/resolve/(:num)']            = 'api/it_ticket/TicketController/resolve/$1';
$route['api/tickets/close/(:num)']              = 'api/it_ticket/TicketController/close/$1';

// APPROVAL RULES
$route['approval-rules']                        = 'api/it_ticket/ApprovalRuleController/index';
$route['approval-rules/show/(:num)']            = 'api/it_ticket/ApprovalRuleController/show/$1';
$route['approval-rules/store']                  = 'api/it_ticket/ApprovalRuleController/store';
$route['approval-rules/update/(:num)']          = 'api/it_ticket/ApprovalRuleController/update/$1';
$route['approval-rules/delete/(:num)']          = 'api/it_ticket/ApprovalRuleController/delete/$1';

// LEVEL RULES
$route['level-rules']                           = 'api/it_ticket/LevelRuleController/index';
$route['level-rules/show/(:num)']               = 'api/it_ticket/LevelRuleController/show/$1';
$route['level-rules/store']                     = 'api/it_ticket/LevelRuleController/store';
$route['level-rules/update/(:num)']             = 'api/it_ticket/LevelRuleController/update/$1';
$route['level-rules/delete/(:num)']             = 'api/it_ticket/LevelRuleController/delete/$1';

// CUSTOM FIELDS
$route['custom-fields']                         = 'api/it_ticket/CustomFieldController/index';
$route['custom-fields/show/(:num)']             = 'api/it_ticket/CustomFieldController/show/$1';
$route['custom-fields/store']                   = 'api/it_ticket/CustomFieldController/store';
$route['custom-fields/update/(:num)']           = 'api/it_ticket/CustomFieldController/update/$1';
$route['custom-fields/delete/(:num)']           = 'api/it_ticket/CustomFieldController/delete/$1';

/*
|--------------------------------------------------------------------------
| END SUPER ADMIN ROUTES
|--------------------------------------------------------------------------
*/

$route['default_controller']  = 'arche_ticket/ticket/index';
// $route['arche_ticket']        = 'arche_ticket/ticket/index';
// $route['arche_ticket/create'] = 'arche_ticket/ticket/create';

// Arche ticket
$route['tickets'] = "arche_ticket/ticket/index";
$route['tickets/list']       = "arche_ticket/ticket/list";
$route['tickets/view/(:num)'] = "arche_ticket/ticket/view/$1";
$route['tickets/reopen/(:num)'] = "arche_ticket/ticket/reopen/$1";

/*
| API Routes - Posts
*/
$route['api/posts']['GET']                  = 'api/posts/index';
$route['api/posts']['POST']                 = 'api/posts/store';
$route['api/posts/([0-9]+)']['GET']         = 'api/posts/show/$1';
$route['api/posts/([0-9]+)']['PUT']         = 'api/posts/update/$1';
$route['api/posts/([0-9]+)']['DELETE']      = 'api/posts/delete/$1';
$route['api/posts/([0-9]+)/exists']['GET']  = 'api/posts/exists/$1';
