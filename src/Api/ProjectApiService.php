<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;

class ProjectApiService
{
    /**
     * The Asana API client instance
     *
     * Handles HTTP requests to the Asana API endpoints with proper authentication
     * and request formatting. This client manages the API connection details and
     * provides methods for making authenticated requests.
     *
     * @var AsanaApiClient An authenticated client for making Asana API requests
     */
    private AsanaApiClient $client;

    /**
     * Constructor
     *
     * Initializes the instance with the provided Asana API client. The client is
     * used to make authenticated requests to the Asana API.
     *
     * @param AsanaApiClient $client An instance of the AsanaApiClient responsible for
     *                               handling API requests and authentication.
     *
     * @return void
     */
    public function __construct(AsanaApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get multiple projects
     *
     * GET /projects
     *
     * Returns a list of projects in a workspace or team that the user has access to.
     * This endpoint provides a way to get multiple projects in a single request according
     * to your search parameters.
     *
     * API Documentation: https://developers.asana.com/reference/getprojects
     *
     * @param string|null $workspace Filter projects by workspace. Can be workspace ID or null.
     *                    This or $team must have a value.
     * @param string|null $team Filter projects by team. Can be team ID or null
     *                     This or $workspace must have a value.
     * @param array $options Query parameters to filter and format results:
     *                      Filtering parameters:
     *                      - archived (boolean): Only return projects whose archived field takes this value
     *                      - limit (int): Maximum number of projects to return. Default is 20
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings")
     * Example: ['opt_fields' => 'name,owner.name,custom_field_settings']
     *                      - opt_pretty (bool): Returns prettier formatting in responses
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is true, returns array containing:
     *               - status: Response status code
     *               - reason: Response reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns decoded response body containing:
     *               - gid: Project's unique identifier
     *               - name: Project name/title
     *               - resource_type: Always "project"
     *               Additional fields if specified in opt_fields
     *
     * @throws AsanaApiException If the API request fails due to:
     *                          - Invalid parameter values
     *                          - Insufficient permissions
     *                          - Rate limiting
     *                          - Network connectivity issues
     */
    public function getProjects(
        ?string $workspace = null,
        ?string $team = null,
        array $options = [],
        bool $fullResponse = false
    ): array {
        // Ensure one of workspace or team is provided
        if (!$workspace && !$team) {
            throw new \InvalidArgumentException('You must provide either a "workspace" or "team" parameter.');
        }

        // Add the provided identifier to options
        if ($workspace) {
            $options['workspace'] = $workspace;
        }
        if ($team) {
            $options['team'] = $team;
        }

        return $this->client->request('GET', 'projects', ['query' => $options], $fullResponse);
    }

    /**
     * Create a project
     *
     * POST /projects
     *
     * Creates a new project in an Asana workspace or team. The project can be given a name,
     * notes, specified team/workspace, and other configurable attributes.
     *
     * API Documentation: https://developers.asana.com/reference/createproject
     *
     * @param array $data Data for creating the project. Supported fields include (but are not limited to):
     *                    Required:
     *                    - workspace (string): GID of workspace to create project in
     *                      AND/OR
     *                    - team (string): GID of team to create project in
     *                    Optional:
     *                    - name (string): Name of the project
     *                    - notes (string): Project description/notes
     *                    - color (string): Color of the project (e.g., "light-green")
     *                    - due_date (string): Due date in YYYY-MM-DD format
     *                    - public (boolean): Whether the project is public to the organization
     *                    - default_view (string): Default view for the project ("list", "board", "calendar", etc.)
     *                    Example: ["name" => "New project", "workspace" => "12345", "notes" => "Project details"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings")
     * Example: ['opt_fields' => 'name,owner.name,custom_field_settings']
     *                      - opt_pretty: Return formatted JSON
     *                      Example: ["opt_fields" => "name,notes,color"]
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is true, returns array containing:
     *               - status: Response status code
     *               - reason: Response reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns decoded response body containing:
     *               - gid: Unique project identifier
     *               - resource_type: Always "project"
     *               - name: Project name/title
     *               Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If the API request fails due to:
     *                         - Missing required fields
     *                         - Invalid field values
     *                         - Insufficient permissions
     *                         - Network connectivity issues
     *                         - Rate limiting
     */
    public function createProject(array $data, array $options = [], bool $fullResponse = false): array
    {
        return $this->client->request(
            'POST',
            'projects',
            ['json' => ['data' => $data], 'query' => $options],
            $fullResponse
        );
    }

    /**
     * Get a project
     *
     * GET /projects/{project_gid}
     *
     * Returns the complete project record for a single project. The project record includes
     * basic metadata (name, notes, status, etc.) along with any custom fields and more
     * as requested via opt_fields.
     *
     * API Documentation: https://developers.asana.com/reference/getproject
     *
     * @param string $projectGid The unique global ID of the project to retrieve. This identifier
     *                        can be found in the project URL or returned from project-related API endpoints.
     *                        Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings")
     * Example: ['opt_fields' => 'name,owner.name,custom_field_settings']
     *                        Common fields include: name, notes, owner, workspace, team, members, followers,
     *                        created_at, modified_at, due_date, current_status
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *                      Example: ["opt_fields" => "name,notes,owner"]
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is true, returns array containing:
     *               - status: Response status code
     *               - reason: Response reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns decoded response body containing:
     *               - gid: Unique project identifier
     *               - resource_type: Always "project"
     *               - name: Project name/title
     *               Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If the API request fails due to invalid project GID, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getProject(string $projectGid, array $options = [], bool $fullResponse = false): array
    {
        return $this->client->request('GET', "projects/$projectGid", ['query' => $options], $fullResponse);
    }

    /**
     * Update a project
     *
     * PUT /projects/{project_gid}
     *
     * Updates the properties of a project. Projects can be updated to change things like their name,
     * notes, due date, and other properties. Any unspecified fields will remain unchanged.
     *
     * API Documentation: https://developers.asana.com/reference/updateproject
     *
     * @param string $projectGid The unique global ID of the project to update. This identifier can
     *                        be found in the project URL or returned from project-related API endpoints.
     *                        Example: "12345"
     * @param array $data The properties of the project to update. Can include:
     *                    - name (string): Name of the project
     *                    - notes (string): Project description/notes
     *                    - color (string): Color of the project (e.g., "light-green")
     *                    - due_date (string): Due date in YYYY-MM-DD format
     *                    - public (boolean): Whether the project is public to the organization
     *                    - owner (string): GID of user to set as project owner
     *                    - archived (boolean): Whether the project is archived
     *                    Example: ["name" => "Updated Project", "notes" => "New description"]
     * @param array $options Optional parameters for the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings")
     * Example: ['opt_fields' => 'name,owner.name,custom_field_settings']
     *                      - opt_pretty: Return formatted JSON
     *                      Example: ["opt_fields" => "name,notes,owner"]
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is true, returns array containing:
     *               - status: Response status code
     *               - reason: Response reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns decoded response body containing:
     *               - gid: Unique project identifier
     *               - resource_type: Always "project"
     *               - name: Updated project name
     *               Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid project GID provided, malformed data,
     *                         insufficient permissions, or network issues occur
     */
    public function updateProject(
        string $projectGid,
        array $data,
        array $options = [],
        bool $fullResponse = false
    ): array {
        return $this->client->request(
            'PUT',
            "projects/$projectGid",
            ['json' => ['data' => $data], 'query' => $options],
            $fullResponse
        );
    }

    /**
     * Delete a project
     *
     * DELETE /projects/{project_gid}
     *
     * Deletes a project. This endpoint may return with a success code before the project has been
     * completely deleted. Also note that while you can delete a single-owner project, you must be an
     * admin in the workspace that contains the project to delete a multi-owned project.
     *
     * API Documentation: https://developers.asana.com/reference/deleteproject
     *
     * @param string $projectGid The unique global ID of the project to delete/trash.
     *                        This identifier can be found in the project URL
     *                        or returned from project-related API endpoints.
     *                        Example: "12345"
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is true, returns array containing:
     *               - status: Response status code
     *               - reason: Response reason phrase
     *               - headers: Response headers
     *               - body: Empty data object {}
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns empty data object:
     *               - data: An empty JSON object {}
     *
     * @throws AsanaApiException If the API request fails due to:
     *                          - Invalid project GID
     *                          - Insufficient permissions to delete the project
     *                          - Network connectivity issues
     *                          - Rate limiting
     */
    public function deleteProject(string $projectGid, bool $fullResponse = false): array
    {
        return $this->client->request('DELETE', "projects/$projectGid", [], $fullResponse);
    }

    /**
     * Duplicate a project
     *
     * POST /projects/{project_gid}/duplicate
     *
     * Creates and returns a job that will duplicate a project, copying its tasks, sections, and structure.
     * The project must be in a premium workspace to use this capability.
     *
     * API Documentation: https://developers.asana.com/reference/duplicateproject
     *
     * @param string $projectGid The unique global ID of the project to duplicate.
     *                        The GID can be found in the project URL or returned from project-related API endpoints.
     *                        Example: "12345"
     * @param array $data Data for the duplicated project. Must include:
     *                    - name (string): Name of the new duplicated project
     *                    Optional:
     *                    - team (string): GID of the team to put the new project in
     *                    - include (array): Additional fields to include in the duplicate
     *                    - schedule_dates (object): Schedule dates to use in the duplicated project with:
     *                      - should_skip_weekends (boolean): Whether to skip weekends during scheduling
     *                      - due_on (string): The due date for the duplicated project
     *                      - start_on (string): The start date for the duplicated project
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is true, returns array containing:
     *               - status: Response status code
     *               - reason: Response reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body containing job data
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns decoded response body containing:
     *               - gid: Unique identifier of the job
     *               - resource_type: Always "job"
     *               - resource_subtype: Type of job
     *               - status: Current job status (e.g. "not_started", "in_progress", "succeeded")
     *               - new_project: Contains the duplicated project data once job is complete
     *
     * @throws AsanaApiException If the API request fails due to invalid project GID, malformed data,
     *                          insufficient permissions, network issues, or rate limiting
     */
    public function duplicateProject(string $projectGid, array $data, bool $fullResponse = false): array
    {
        return $this->client->request(
            'POST',
            "projects/$projectGid/duplicate",
            ['json' => ['data' => $data]],
            $fullResponse
        );
    }

    /**
     * Get projects a task is in
     *
     * GET /tasks/{task_gid}/projects
     *
     * Returns a list of projects that the specified task is a member of. A task can
     * be associated with multiple projects.
     *
     * API Documentation: https://developers.asana.com/reference/getprojectsfortask
     *
     * @param string $taskGid The unique global ID of the task to get projects for.
     *                       This identifier can be found in the task URL or returned from
     *                       task-related API endpoints.
     * @param array $options Optional parameters for customizing the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings")
     * Example: ['opt_fields' => 'name,owner.name,custom_field_settings']
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *                      - limit (int): Results to return per page (1-100)
     *                      - offset (string): Pagination offset token
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is true, returns array containing:
     *               - status: Response status code
     *               - reason: Response reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns decoded response body containing:
     *               - gid: Project identifier
     *               - name: Project name
     *               - resource_type: Always "project"
     *               Additional fields if specified in opt_fields
     *
     * @throws AsanaApiException If invalid task GID provided, permission errors,
     *                          network issues, or rate limiting occurs
     */
    public function getProjectsForTask(string $taskGid, array $options = [], bool $fullResponse = false): array
    {
        return $this->client->request('GET', "tasks/$taskGid/projects", ['query' => $options], $fullResponse);
    }

    /**
     * Get a team's projects
     *
     * GET /teams/{team_gid}/projects
     *
     * Returns the projects in a team. Teams are only available on Asana Premium or Business plans.
     * This endpoint requires the team to be public to the authenticated user or for the user to be an
     * admin of the team.
     *
     * API Documentation: https://developers.asana.com/reference/getprojectsforteam
     *
     * @param string $teamGid The unique global ID of the team to get projects from.
     *                      This identifier can be found in the team URL or returned from
     *                      team-related API endpoints.
     * @param array $options Optional parameters for customizing the request:
     *                      - archived (boolean): Only return projects whose archived field matches this value
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings")
     * Example: ['opt_fields' => 'name,owner.name,custom_field_settings']
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *                      - limit (int): Results to return per page (1-100)
     *                      - offset (string): Pagination offset token
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is true, returns array containing:
     *               - status: Response status code
     *               - reason: Response reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns decoded response body containing:
     *               - gid: Project identifier
     *               - name: Project name
     *               - resource_type: Always "project"
     *               Additional fields if specified in opt_fields
     *
     * @throws AsanaApiException If invalid team GID provided, permission errors,
     *                          network issues, or rate limiting occurs
     */
    public function getProjectsForTeam(string $teamGid, array $options = [], bool $fullResponse = false): array
    {
        return $this->client->request('GET', "teams/$teamGid/projects", ['query' => $options], $fullResponse);
    }

    /**
     * Create a project in a team
     *
     * POST /teams/{team_gid}/projects
     *
     * Creates a project and adds it to the specified team. This endpoint creates a project from
     * scratch, setting its workspace to be the same workspace containing the team.
     *
     * API Documentation: https://developers.asana.com/reference/createprojectforteam
     *
     * @param string $teamGid The unique global ID of the team in which to create the project.
     *                      This identifier can be found in the team URL or returned from
     *                      team-related API endpoints.
     * @param array $data Data for creating the project.
     *                    Optional:
     *                    - name (string): Name of the project
     *                    - notes (string): Project description/notes
     *                    - color (string): Color of the project (e.g., "light-green")
     *                    - due_date (string): Due date in YYYY-MM-DD format
     *                    - public (boolean): Whether the project is public to the organization
     *                    - default_view (string): Default view for the project ("list", "board", "calendar", etc.)
     *                    Example: ["name" => "New team project", "notes" => "Project details"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings")
     * Example: ['opt_fields' => 'name,owner.name,custom_field_settings']
     *                      - opt_pretty: Return formatted JSON
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is true, returns array containing:
     *               - status: Response status code
     *               - reason: Response reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns decoded response body containing:
     *               - gid: Unique project identifier
     *               - resource_type: Always "project"
     *               - name: Project name/title
     *               Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid team GID provided, missing required fields,
     *                          insufficient permissions, or network issues occur
     */
    public function createProjectInTeam(
        string $teamGid,
        array $data,
        array $options = [],
        bool $fullResponse = false
    ): array {
        return $this->client->request(
            'POST',
            "teams/$teamGid/projects",
            ['json' => ['data' => $data], 'query' => $options],
            $fullResponse
        );
    }

    /**
     * Get all projects in a workspace
     *
     * GET /workspaces/{workspace_gid}/projects
     *
     * Returns the projects in a workspace. Includes archived projects by default.
     *
     * API Documentation: https://developers.asana.com/reference/getprojectsforworkspace
     *
     * @param string $workspaceGid The unique global ID of the workspace to get projects from.
     *                           This identifier can be found in the workspace URL or returned from
     *                           workspace-related API endpoints.
     * @param array $options Optional parameters for customizing the request:
     *                      - archived (boolean): Only return projects whose archived field matches this value
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings")
     * Example: ['opt_fields' => 'name,owner.name,custom_field_settings']
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *                      - limit (int): Results to return per page (1-100)
     *                      - offset (string): Pagination offset token
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is true, returns array containing:
     *               - status: Response status code
     *               - reason: Response reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns decoded response body containing:
     *               - gid: Project identifier
     *               - name: Project name
     *               - resource_type: Always "project"
     *               Additional fields if specified in opt_fields
     *
     * @throws AsanaApiException If invalid workspace GID provided, permission errors,
     *                          network issues, or rate limiting occurs
     */
    public function getProjectsForWorkspace(
        string $workspaceGid,
        array $options = [],
        bool $fullResponse = false
    ): array {
        return $this->client->request('GET', "workspaces/$workspaceGid/projects", ['query' => $options], $fullResponse);
    }

    /**
     * Create a project in a workspace
     *
     * POST /workspaces/{workspace_gid}/projects
     *
     * Creates a project in the specified workspace.
     *
     * API Documentation: https://developers.asana.com/reference/createproject
     *
     * @param string $workspaceGid The unique global ID of the workspace to create the project in.
     *                           This identifier can be found in the workspace URL or returned from
     *                           workspace-related API endpoints.
     * @param array $data Data for creating the project. Must include:
     *                    Optional:
     *                    - name (string): Name of the project
     *                    - notes (string): Project description/notes
     *                    - color (string): Color of the project (e.g., "light-green")
     *                    - due_date (string): Due date in YYYY-MM-DD format
     *                    - public (boolean): Whether the project is public to the organization
     *                    - default_view (string): Default view for the project ("list", "board", "calendar", etc.)
     *                    Example: ["name" => "New workspace project", "notes" => "Project details"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings")
     * Example: ['opt_fields' => 'name,owner.name,custom_field_settings']
     *                      - opt_pretty: Return formatted JSON
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is true, returns array containing:
     *               - status: Response status code
     *               - reason: Response reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns decoded response body containing:
     *               - gid: Unique project identifier
     *               - resource_type: Always "project"
     *               - name: Project name/title
     *               Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid workspace GID provided, missing required fields,
     *                          insufficient permissions, or network issues occur
     */
    public function createProjectInWorkspace(
        string $workspaceGid,
        array $data,
        array $options = [],
        bool $fullResponse = false
    ): array {
        return $this->client->request(
            'POST',
            "workspaces/$workspaceGid/projects",
            ['json' => ['data' => $data], 'query' => $options],
            $fullResponse
        );
    }

    /**
     * Add a custom field to a project
     *
     * POST /projects/{project_gid}/addCustomFieldSetting
     *
     * Adds a custom field to a project. Custom fields are defined per-organization and must exist
     * before they can be added to a project. By default, a custom field in a project may not be
     * associated with another project in the same organization, but this can be controlled by the project.
     *
     * API Documentation: https://developers.asana.com/reference/addcustomfieldsettingforproject
     *
     * @param string $projectGid The unique global ID of the project to add the custom field to.
     *                        This identifier can be found in the project URL or returned from
     *                        project-related API endpoints.
     * @param array $data Data for adding the custom field setting. Must include:
     *                    - custom_field (string): The GID of the custom field to add
     *                    Optional:
     *                    - is_important (boolean): Whether the custom field should be displayed prominently
     *                    - insert_before (string): GID of a custom field setting to insert this one before
     *                    - insert_after (string): GID of a custom field setting to insert this one after
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is true, returns array containing:
     *               - status: Response status code
     *               - reason: Response reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body containing the updated project data
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns decoded response body containing
     *               the updated project with the custom field setting added
     *
     * @throws AsanaApiException If invalid project GID provided, invalid custom field GID,
     *                          insufficient permissions, or network issues occur
     */
    public function addCustomFieldToProject(string $projectGid, array $data, bool $fullResponse = false): array
    {
        return $this->client->request(
            'POST',
            "projects/$projectGid/addCustomFieldSetting",
            ['json' => ['data' => $data]],
            $fullResponse
        );
    }

    /**
     * Remove a custom field from a project
     *
     * POST /projects/{project_gid}/removeCustomFieldSetting
     *
     * Removes a custom field from a project. Note that this does not delete the custom field,
     * it just removes the custom field from the specified project.
     *
     * API Documentation: https://developers.asana.com/reference/removecustomfieldsettingforproject
     *
     * @param string $projectGid The unique global ID of the project to remove the custom field from.
     *                        This identifier can be found in the project URL or returned from
     *                        project-related API endpoints.
     * @param array $data Data for removing the custom field setting. Must include:
     *                    - custom_field (string): The GID of the custom field to remove
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is true, returns array containing:
     *               - status: Response status code
     *               - reason: Response reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body containing the updated project data
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns decoded response body containing
     *               the updated project with the custom field setting removed
     *
     * @throws AsanaApiException If invalid project GID provided, invalid custom field GID,
     *                          insufficient permissions, or network issues occur
     */
    public function removeCustomFieldFromProject(string $projectGid, array $data, bool $fullResponse = false): array
    {
        return $this->client->request(
            'POST',
            "projects/$projectGid/removeCustomFieldSetting",
            ['json' => ['data' => $data]],
            $fullResponse
        );
    }

    /**
     * Get a project's custom fields
     *
     * GET /projects/{project_gid}/custom_field_settings
     *
     * Returns a list of all the custom fields settings on a project, in compact form.
     * These are custom fields that the project has direct access to and can be seen
     * in the project's details.
     *
     * API Documentation: https://developers.asana.com/reference/getcustomfieldsettingsforproject
     *
     * @param string $projectGid The unique global ID of the project to get custom field settings from.
     *                        This identifier can be found in the project URL or returned from
     *                        project-related API endpoints.
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings")
     * Example: ['opt_fields' => 'name,owner.name,custom_field_settings']
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *                      - limit (int): Results to return per page (1-100)
     *                      - offset (string): Pagination offset token
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is true, returns array containing:
     *               - status: Response status code
     *               - reason: Response reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body containing custom field settings
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns decoded response body containing:
     *               - gid: Custom field setting identifier
     *               - custom_field: Object with the custom field information
     *               - is_important: Whether the custom field is prominently displayed
     *               - project: GID of the parent project
     *               - resource_type: Always "custom_field_setting"
     *               Additional fields if specified in opt_fields
     *
     * @throws AsanaApiException If invalid project GID provided, permission errors,
     *                          network issues, or rate limiting occurs
     */
    public function getCustomFieldsForProject(
        string $projectGid,
        array $options = [],
        bool $fullResponse = false
    ): array {
        return $this->client->request(
            'GET',
            "projects/$projectGid/custom_field_settings",
            ['query' => $options],
            $fullResponse
        );
    }

    /**
     * Get task count of a project
     *
     * GET /projects/{project_gid}/task_counts
     *
     * Returns the number of tasks within the specified project, grouped by completion status.
     * This is useful for understanding project progress and workload.
     *
     * API Documentation: https://developers.asana.com/reference/gettaskcountsforproject
     *
     * @param string $projectGid The unique global ID of the project to get task counts for.
     *                        This identifier can be found in the project URL or returned from
     *                        project-related API endpoints.
     * @param array $options Optional parameters for the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings")
     * Example: ['opt_fields' => 'name,owner.name,custom_field_settings']
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is true, returns array containing:
     *               - status: Response status code
     *               - reason: Response reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body containing task count data
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns decoded response body containing:
     *               - num_incomplete_tasks: Number of incomplete tasks in the project
     *               - num_completed_tasks: Number of completed tasks in the project
     *               - num_tasks: Total number of tasks in the project
     *
     * @throws AsanaApiException If invalid project GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getTaskCountsForProject(string $projectGid, array $options = [], bool $fullResponse = false): array
    {
        return $this->client->request('GET', "projects/$projectGid/task_counts", ['query' => $options], $fullResponse);
    }

    /**
     * Add users to a project
     *
     * POST /projects/{project_gid}/addMembers
     *
     * Adds the specified list of users as members of the project. Users are
     * immediately able to collaborate on the project, get notifications, and
     * gain access to the project based on their role.
     *
     * API Documentation: https://developers.asana.com/reference/addmembersforproject
     *
     * @param string $projectGid The unique global ID of the project to add members to.
     *                        This identifier can be found in the project URL or returned from
     *                        project-related API endpoints.
     * @param array $members An array of user GIDs representing the users to add to the project.
     *                       Each GID should be a string that uniquely identifies a user in Asana.
     *                       Example: ['12345', '67890']
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings")
     * Example: ['opt_fields' => 'name,owner.name,custom_field_settings']
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is true, returns array containing:
     *               - status: Response status code
     *               - reason: Response reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns decoded response body containing
     *               the updated project with the new members added
     *
     * @throws AsanaApiException If invalid project GID provided, invalid user GIDs,
     *                          insufficient permissions, or network issues occur
     */
    public function addMembersToProject(
        string $projectGid,
        array $members,
        array $options = [],
        bool $fullResponse = false
    ): array {
        return $this->client->request(
            'POST',
            "projects/$projectGid/addMembers",
            ['json' => ['data' => ['members' => $members]], 'query' => $options],
            $fullResponse
        );
    }

    /**
     * Remove users from a project
     *
     * POST /projects/{project_gid}/removeMembers
     *
     * Removes the specified list of users as members of the project. Users will
     * immediately lose access to the project and will no longer receive notifications
     * unless they remain added as followers.
     *
     * API Documentation: https://developers.asana.com/reference/removemembersforproject
     *
     * @param string $projectGid The unique global ID of the project from which to remove members.
     *                        This identifier can be found in the project URL or returned from
     *                        project-related API endpoints.
     * @param array $members An array of user GIDs representing the users to remove from the project.
     *                       Each GID should be a string that uniquely identifies a user in Asana.
     *                       Example: ['12345', '67890']
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings")
     * Example: ['opt_fields' => 'name,owner.name,custom_field_settings']
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is true, returns array containing:
     *               - status: Response status code
     *               - reason: Response reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns decoded response body containing
     *               the updated project with the members removed
     *
     * @throws AsanaApiException If invalid project GID provided, invalid user GIDs,
     *                          insufficient permissions, or network issues occur
     */
    public function removeMembersFromProject(
        string $projectGid,
        array $members,
        array $options = [],
        bool $fullResponse = false
    ): array {
        return $this->client->request(
            'POST',
            "projects/$projectGid/removeMembers",
            ['json' => ['data' => ['members' => $members]], 'query' => $options],
            $fullResponse
        );
    }

    /**
     * Add followers to a project
     *
     * POST /projects/{project_gid}/addFollowers
     *
     * Adds the specified list of users as followers of the project. Followers receive notifications
     * when the project is changed, but do not necessarily have permissions to modify the project.
     *
     * API Documentation: https://developers.asana.com/reference/addfollowersforproject
     *
     * @param string $projectGid The unique global ID of the project to add followers to.
     *                        This identifier can be found in the project URL or returned from
     *                        project-related API endpoints.
     * @param array $followers An array of user GIDs representing the followers to add to the project.
     *                       Each GID should be a string that uniquely identifies a user in Asana.
     *                       Example: ['12345', '67890']
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                       (e.g., "name,owner.name,custom_field_settings")
     * Example: ['opt_fields' => 'name,owner.name,custom_field_settings']
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is true, returns array containing:
     *               - status: Response status code
     *               - reason: Response reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns decoded response body containing
     *               the updated project with the new followers added
     *
     * @throws AsanaApiException If invalid project GID provided, invalid user GIDs,
     *                          insufficient permissions, or network issues occur
     */
    public function addFollowersToProject(
        string $projectGid,
        array $followers,
        array $options = [],
        bool $fullResponse = false
    ): array {
        return $this->client->request(
            'POST',
            "projects/$projectGid/addFollowers",
            ['json' => ['data' => ['followers' => $followers]], 'query' => $options],
            $fullResponse
        );
    }

    /**
     * Remove followers from a project
     *
     * POST /projects/{project_gid}/removeFollowers
     *
     * Removes the specified list of users from following the project. Followers receive notifications
     * when the project is changed, and removing them will stop these notifications.
     *
     * API Documentation: https://developers.asana.com/reference/removefollowersforproject
     *
     * @param string $projectGid The unique global ID of the project from which to remove followers.
     *                        This identifier can be found in the project URL or returned from
     *                        project-related API endpoints.
     * @param array $followers An array of user GIDs representing the followers to remove from the project.
     *                       Each GID should be a string that uniquely identifies a user in Asana.
     *                       Example: ['12345', '67890']
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                       (e.g., "name,owner.name,custom_field_settings")
     * Example: ['opt_fields' => 'name,owner.name,custom_field_settings']
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is true, returns array containing:
     *               - status: Response status code
     *               - reason: Response reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns decoded response body containing
     *               the updated project with the followers removed
     *
     * @throws AsanaApiException If invalid project GID provided, invalid user GIDs,
     *                          insufficient permissions, or network issues occur
     */
    public function removeFollowersFromProject(
        string $projectGid,
        array $followers,
        array $options = [],
        bool $fullResponse = false
    ): array {
        return $this->client->request(
            'POST',
            "projects/$projectGid/removeFollowers",
            ['json' => ['data' => ['followers' => $followers]], 'query' => $options],
            $fullResponse
        );
    }

    /**
     * Create a project template from a project
     *
     * POST /projects/{project_gid}/saveAsTemplate
     *
     * Creates a project template from an existing project. The new template will be in the same
     * workspace as the given project. Properties such as task names, descriptions, notes,
     * assignees, dependencies, and custom fields are preserved in the template.
     *
     * API Documentation: https://developers.asana.com/reference/projectsaveastemplate
     *
     * @param string $projectGid The unique global ID of the project to use as a basis for creating a template.
     *                        This identifier can be found in the project URL or returned from
     *                        project-related API endpoints.
     * @param array $data Data for creating the project template. Must include:
     *                    - name (string): Name of the new template
     *                    Optional:
     *                    - public (boolean): Whether the template is public to the team
     *                    Example: ["name" => "Marketing Campaign Template"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings")
     * Example: ['opt_fields' => 'name,owner.name,custom_field_settings']
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is true, returns array containing:
     *               - status: Response status code
     *               - reason: Response reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body containing project template data
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns decoded response body containing:
     *               - gid: Unique identifier of the template
     *               - resource_type: Always "project_template"
     *               - name: Name of the template
     *               Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid project GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function createProjectTemplateFromProject(
        string $projectGid,
        array $data,
        array $options = [],
        bool $fullResponse = false
    ): array {
        return $this->client->request(
            'POST',
            "projects/$projectGid/saveAsTemplate",
            ['json' => ['data' => $data], 'query' => $options],
            $fullResponse
        );
    }
}
