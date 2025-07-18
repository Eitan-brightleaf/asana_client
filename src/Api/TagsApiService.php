<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;

class TagsApiService
{
    /**
     * An HTTP client instance configured to interact with the Asana API.
     *
     * This property stores an instance of AsanaApiClient which handles all HTTP communication
     * with the Asana API endpoints. It provides authenticated access to API resources and
     * manages request/response handling.
     */
    private AsanaApiClient $client;

    /**
     * Constructor for initializing the service with an Asana API client.
     *
     * Sets up the service instance using the provided Asana API client.
     *
     * @param AsanaApiClient $client The Asana API client instance used to interact with the Asana API.
     *
     * @return void
     */
    public function __construct(AsanaApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get multiple tags
     *
     * GET /tags
     *
     * Returns a list of tags in the specified workspace or organization. Tags are used to help
     * categorize and sort tasks, making them easier to find and manage.
     *
     * API Documentation: https://developers.asana.com/reference/gettags
     *
     * @param string $workspace The unique identifier (GID) of the workspace to get tags from
     * @param array $options Query parameters to filter and format results:
     *                      Filtering parameters:
     *                      - limit (int): Maximum number of tags to return. Default is 20
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns prettier formatting in responses
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If the API request fails due to:
     *                          - Invalid parameter values
     *                          - Insufficient permissions
     *                          - Rate limiting
     *                          - Network connectivity issues
     */
    public function getTags(string $workspace, array $options = [], int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        // Include workspace in options
        $options['workspace'] = $workspace;

        return $this->client->request('GET', 'tags', ['query' => $options], $responseType);
    }

    /**
     * Create a tag
     *
     * POST /tags
     *
     * Creates a new tag in a workspace. Every tag is required to be created in a specific workspace,
     * and this cannot be changed once set.
     *
     * API Documentation: https://developers.asana.com/reference/createtag
     *
     * @param array $data Data for creating the tag. Supported fields include:
     *                    Required:
     *                    - workspace (string): The workspace to create the tag in
     *                    Optional:
     *                    - name (string): Name of the tag
     *                    - color (string): Color of the tag. Either "dark-pink", "dark-green",
     *                      "dark-blue", "dark-red", "dark-teal", "dark-brown", "dark-orange",
     *                      "dark-purple", "dark-warm-gray", "light-pink", "light-green", "light-blue",
     *                      "light-red", "light-teal", "light-brown", "light-orange", "light-purple",
     *                      or "light-warm-gray"
     *                    - notes (string): Free-form textual information associated with the tag
     *
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If missing required fields, invalid field values,
     *                          insufficient permissions, network issues, or rate limiting occurs
     */
    public function createTag(array $data, array $options = [], int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request(
            'POST',
            'tags',
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Get a tag
     *
     * GET /tags/{tag_gid}
     *
     * Returns the complete tag record for a single tag.
     *
     * API Documentation: https://developers.asana.com/reference/gettag
     *
     * @param string $tagGid The unique global ID of the tag to retrieve. This identifier
     *                       can be found in the tag URL or returned from tag-related API endpoints.
     *                       Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If invalid tag GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getTag(string $tagGid, array $options = [], int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request('GET', "tags/$tagGid", ['query' => $options], $responseType);
    }

    /**
     * Update a tag
     *
     * PUT /tags/{tag_gid}
     *
     * Updates the properties of a tag. Only the fields provided in the data block will be updated;
     * any unspecified fields will remain unchanged.
     *
     * API Documentation: https://developers.asana.com/reference/updatetag
     *
     * @param string $tagGid The unique global ID of the tag to update. This identifier can
     *                       be found in the tag URL or returned from tag-related API endpoints.
     *                       Example: "12345"
     * @param array $data The properties of the tag to update. Can include:
     *                    - name (string): Name of the tag
     *                    - color (string): Color of the tag. See createTag for allowed values
     *                    - notes (string): Free-form textual information associated with the tag
     *                    Example: ["name" => "Updated Tag", "color" => "light-green"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If invalid tag GID provided, malformed data,
     *                          insufficient permissions, or network issues occur
     */
    public function updateTag(string $tagGid, array $data, array $options = [], int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request(
            'PUT',
            "tags/$tagGid",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Delete a tag
     *
     * DELETE /tags/{tag_gid}
     *
     * Deletes a tag. This does not remove the tag from any tasks; it only deletes the tag resource itself.
     *
     * API Documentation: https://developers.asana.com/reference/deletetag
     *
     * @param string $tagGid The unique global ID of the tag to delete.
     *                       This identifier can be found in the tag URL
     *                       or returned from tag-related API endpoints.
     *                       Example: "12345"
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If invalid tag GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function deleteTag(string $tagGid, int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request('DELETE', "tags/$tagGid", [], $responseType);
    }

    /**
     * Get tasks from a tag
     *
     * GET /tags/{tag_gid}/tasks
     *
     * Returns the tasks that have this tag. Tasks can have multiple tags, and
     * this endpoint allows retrieving all tasks with a specific tag.
     *
     * API Documentation: https://developers.asana.com/reference/gettasksfortag
     *
     * @param string $tagGid The unique global ID of the tag for which to get tasks.
     * @param array $options Optional parameters to customize the request.
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If invalid tag GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getTasksForTag(string $tagGid, array $options = [], int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request('GET', "tags/$tagGid/tasks", ['query' => $options], $responseType);
    }

    /**
     * Get tags in a workspace
     *
     * GET /workspaces/{workspace_gid}/tags
     *
     * Returns the tags available in the specified workspace. Tags are used to categorize
     * and label tasks within a workspace.
     *
     * API Documentation: https://developers.asana.com/reference/gettagsforworkspace
     *
     * @param string $workspaceGid The unique global ID of the workspace to get tags from.
     * @param array $options Optional parameters to customize the request.
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If invalid workspace GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getTagsForWorkspace(string $workspaceGid, array $options = [], int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request('GET', "workspaces/$workspaceGid/tags", ['query' => $options], $responseType);
    }

    /**
     * Create a tag in a workspace
     *
     * POST /workspaces/{workspace_gid}/tags
     *
     * Creates a new tag in a workspace. This is a shortcut for creating a tag in a specific workspace
     * rather than specifying the workspace in the data.
     *
     * API Documentation: https://developers.asana.com/reference/createtagforworkspace
     *
     * @param string $workspaceGid The unique global ID of the workspace to create the tag in.
     * @param array $data Data for creating the tag.
     * @param array $options Optional parameters to customize the request.
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If invalid workspace GID provided, malformed data,
     *                          insufficient permissions, or network issues occur
     */
    public function createTagInWorkspace(
        string $workspaceGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request(
            'POST',
            "workspaces/$workspaceGid/tags",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }
}
