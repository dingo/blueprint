<?php

namespace Dingo\Blueprint\Tests\Stubs;

/**
 * Users Resource
 *
 * @Resource("Users", uri="/users")
 */
class UsersResourceStub
{
    /**
     * Show all users
     *
     * Get a JSON representation of all registered users.
     *
     * @Get("/")
     * @Versions({"v1"})
     * @Response(200, body={
     *      {"id": 5, "name": "jason"},
     *      {"id": 13, "name": "bob"}
     * })
     */
    public function index()
    {
        //
    }

    /**
     * Show existing user
     *
     * Get a JSON representation of an existing user.
     *
     * @Get("/{id}")
     * @Parameters({
     *      @Parameter("id", description="ID of user to retrieve", type="integer", required=true)
     * })
     * @Transaction({
     *      @Response(200, body={"id": 5, "name": "jason"}),
     *      @Response(404, body={"message": "User could not be found."})
     * })
     */
    public function show($id)
    {
        //
    }

    /**
     * Create new user
     *
     * Create a new user.
     *
     * @Post("/")
     * @Transaction({
     *      @Request({
     *          "name": "jason",
     *          "email": "jason@jason.com",
     *          "password": "1234567"
     *      }),
     *      @Response(200, body={"id": 10, "name": "jason", "email": "jason@jason.com"}),
     *      @Response(422, body={
     *          "message": "Unable to create user due to validation errors.",
     *          "errors": {
     *              "name": {"The name already exists."}
     *          }
     *      })
     * })
     */
    public function store()
    {
        //
    }
}
