<?php

namespace Dingo\Blueprint\Tests\Stubs;

/**
 * @Resource("Account", uri="/accounts")
 * @DataStructures({
 *     @Type("Account", type="object", properties={
 *          @Property("name", type="string", description="The account name", sample="Savings"),
 *          @Property("balance", type="number", description="The account balance", sample=1200),
 *     }),
 *     @Type("Deposit", type=@Type("Account"))
 * })
 */
class AccountController
{
    /**
     * Show all accounts.
     *
     * @Get("/")
     * @Response(200, attributes={
     *      @Attribute("success", type="boolean", sample="true", required=true, description="Status of the request"),
     *      @Attribute("data", type={@Type("Account")}, description="The account's data", required=true)
     * })
     */
    public function index()
    {
    }

    /**
     * Show a specific account.
     *
     * @Get("/1")
     * @Response(200, attributes={
     *      @Attribute("success", type="boolean", sample="true", required=true, description="Status of the request"),
     *      @Attribute("data", type=@Type("Deposit"), description="The account's data", required=true)
     * })
     */
    public function show()
    {
    }

    /**
     * Create a new account.
     *
     * @Post("/")
     * @Transaction({
     *      @Request(type=@Type("Account")),
     *      @Response(200, type=@Type("Account"))
     * })
     */
    public function store()
    {
        //
    }
}
