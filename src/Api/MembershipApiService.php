<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;

class MembershipApiService
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
     * Get multiple memberships
     *
     * GET /memberships
     *
     * Returns the compact membership records for the memberships matching the given filters.
     * Memberships represent connections between non-project objects and relevant users,
     * indicating a user's access and permissions in relation to that object.
     *
     * API Documentation: https://developers.asana.com/reference/getmemberships
     *
     * @param array $options Query parameters to filter and format results:
     *                      Required filtering parameters (at least one of):
     *                      - parent (string): A resource ID to filter memberships by parent
     *                        (project, goal, portfolio, or custom_field)
     *                      - portfolio (string): A portfolio ID to filter memberships by portfolio
     *                      Optional filtering parameters:
     *                      - member (string): A team or user ID to filter memberships by member
     *                      - limit (int): Maximum number of items to return. Default is 20
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns prettier formatting in responses
     * @param bool $fullResponse Whether to return the full response details including headers and request info
     *
     * @return array If $fullResponse is true, returns complete response array including:
     *               - status: HTTP status code
     *               - headers: Response headers
     *               - body: Parsed response body with list of memberships
     *               - request: Original request details
     *               If $fullResponse is false, returns just the list of memberships containing:
     *               - gid: Membership's unique identifier
     *               - resource_type: Always "membership"
     *               Additional fields if specified in opt_fields
     *
     * @throws AsanaApiException If the API request fails due to:
     *                          - Missing required parameters
     *                          - Invalid parameter values
     *                          - Insufficient permissions
     *                          - Rate limiting
     *                          - Network connectivity issues
     */
    public function getMemberships(array $options = [], bool $fullResponse = false): array
    {
        return $this->client->request('GET', 'memberships', ['query' => $options], $fullResponse);
    }

    /**
     * Create a membership
     *
     * POST /memberships
     *
     * Creates a new membership in a parent object (goal, project, or portfolio).
     * Memberships provide a way to add users as members of top-level objects.
     * Portfolios and custom fields only support users as members.
     *
     * API Documentation: https://developers.asana.com/reference/createmembership
     *
     * @param array $data Data for creating the membership. Supported fields include:
     *                    Required:
     *                    - parent (string): The parent id of the membership (goal, project, portfolio, or custom_field)
     *                    - member (string): The gid of the user or team being added as a member
     *                    Optional:
     *                    - access_level (string): Sets the access level for the member.
     *                         Goals can have access levels 'editor' or 'commenter'. Projects can have
     *                         access levels 'admin', 'editor' or 'commenter'. Portfolios can have access
     *                         levels 'admin', 'editor' or 'viewer'. Custom Fields can
        *                      have access levels 'admin', 'editor' or 'user'.
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param bool $fullResponse Whether to return the full response details including headers and request info
     *
     * @return array If $fullResponse is true, returns complete response array including:
     *               - status: HTTP status code
     *               - headers: Response headers
     *               - body: Parsed response body with created membership data
     *               - request: Original request details
     *               If $fullResponse is false, returns just the created membership data including:
     *               - gid: Membership's unique identifier
     *               - resource_type: Always "membership"
     *               Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If the API request fails due to:
     *                          - Missing required fields
     *                          - Invalid field values
     *                          - Insufficient permissions
     *                          - Network connectivity issues
     *                          - Rate limiting
     */
    public function createMembership(array $data, array $options = [], bool $fullResponse = false): array
    {
        return $this->client->request(
            'POST',
            'memberships',
            ['json' => ['data' => $data], 'query' => $options],
            $fullResponse
        );
    }

    /**
     * Get a membership
     *
     * GET /memberships/{membership_gid}
     *
     * Returns the complete membership record for a single membership.
     *
     * API Documentation: https://developers.asana.com/reference/getmembership
     *
     * @param string $membershipGid The unique global ID of the membership to retrieve.
     *                              This identifier can be found in the membership URL or
     *                              returned from membership-related API endpoints.
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param bool $fullResponse Whether to return the full response details including headers and request info
     *
     * @return array If $fullResponse is true, returns complete response array including:
     *               - status: HTTP status code
     *               - headers: Response headers
     *               - body: Parsed response body with membership data
     *               - request: Original request details
     *               If $fullResponse is false, returns just the membership record containing:
     *               - gid: Membership's unique identifier
     *               - resource_type: Always "membership"
     *               - parent: The parent object (portfolio, project, goal, or custom_field) of this membership
     *               - member: The member (user or team) in this membership
     *               - access_level: The access level of the membership (admin, editor, commenter, viewer, etc.)
     *               Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid membership GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getMembership(string $membershipGid, array $options = [], bool $fullResponse = false): array
    {
        return $this->client->request('GET', "memberships/$membershipGid", ['query' => $options], $fullResponse);
    }

    /**
     * Update a membership
     *
     * PUT /memberships/{membership_gid}
     *
     * Updates the properties of a membership. Only the fields provided in the data block
     * will be updated; any unspecified fields will remain unchanged.
     *
     * API Documentation: https://developers.asana.com/reference/updatemembership
     *
     * @param string $membershipGid The unique global ID of the membership to update.
     *                              This identifier can be found in the membership URL or
     *                              returned from membership-related API endpoints.
     * @param array $data The properties of the membership to update. Can include:
     *                    - access_level (string): The updated access level for the membership.
     *                      Allowed values depend on the parent type:
     *                      - Goals: 'editor', 'commenter'
     *                      - Projects: 'admin', 'editor', 'commenter'
     *                      - Portfolios: 'admin', 'editor', 'viewer'
     *                      - Custom Fields: 'admin', 'editor', 'user'
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param bool $fullResponse Whether to return the full response details including headers and request info
     *
     * @return array If $fullResponse is true, returns complete response array including:
     *               - status: HTTP status code
     *               - headers: Response headers
     *               - body: Parsed response body with updated membership data
     *               - request: Original request details
     *               If $fullResponse is false, returns just the updated membership data including:
     *               - gid: Membership's unique identifier
     *               - resource_type: Always "membership"
     *               - parent: The parent object (portfolio, project, goal, or custom_field) of this membership
     *               - member: The member (user or team) in this membership
     *               - access_level: The updated access level of the membership
     *               Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid membership GID provided, malformed data,
     *                          insufficient permissions, or network issues occur
     */
    public function updateMembership(
        string $membershipGid,
        array $data,
        array $options = [],
        bool $fullResponse = false
    ): array {
        return $this->client->request(
            'PUT',
            "memberships/$membershipGid",
            ['json' => ['data' => $data], 'query' => $options],
            $fullResponse
        );
    }

    /**
     * Delete a membership
     *
     * DELETE /memberships/{membership_gid}
     *
     * Deletes a membership. This is the way to remove a user or team from a
     * portfolio, project, goal, or custom_field.
     *
     * API Documentation: https://developers.asana.com/reference/deletemembership
     *
     * @param string $membershipGid The unique global ID of the membership to delete.
     *                              This identifier can be found in the membership URL or
     *                              returned from membership-related API endpoints.
     * @param bool $fullResponse Whether to return the full response details including headers and request info
     *
     * @return array If $fullResponse is true, returns complete response array including:
     *               - status: HTTP status code
     *               - headers: Response headers
     *               - body: Empty data object
     *               - request: Original request details
     *               If $fullResponse is false, returns just an empty data object:
     *               - data: An empty JSON object {}
     *
     * @throws AsanaApiException If the API request fails due to:
     *                          - Invalid membership GID
     *                          - Insufficient permissions to delete the membership
     *                          - Network connectivity issues
     *                          - Rate limiting
     */
    public function deleteMembership(string $membershipGid, bool $fullResponse = false): array
    {
        return $this->client->request('DELETE', "memberships/$membershipGid", [], $fullResponse);
    }
}
