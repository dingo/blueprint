<?php

namespace Dingo\Blueprint\Tests;

use ReflectionMethod;
use Dingo\Blueprint\Blueprint;
use PHPUnit_Framework_TestCase;
use Illuminate\Support\Collection;
use Doctrine\Common\Annotations\SimpleAnnotationReader;

class ActionTest extends PHPUnit_Framework_TestCase
{
    public function testGeneratingBlueprintForSingleResource()
    {
        $resources = new Collection([new Stubs\UsersResourceStub]);

        $blueprint = new Blueprint(new SimpleAnnotationReader);

        $expected = <<<EOT
FORMAT: 1A

# testing

# Users [/users]
Users Resource

## Show all users [GET /users]
Get a JSON representation of all registered users.

+ Response 200 (application/json)
    + Body

            [
                {
                    "id": 5,
                    "name": "jason"
                },
                {
                    "id": 13,
                    "name": "bob"
                }
            ]

## Show existing user [GET /users/{id}]
Get a JSON representation of an existing user.

+ Parameters
    + id (integer, required) - ID of user to retrieve

+ Response 200 (application/json)
    + Body

            {
                "id": 5,
                "name": "jason"
            }

+ Response 404 (application/json)
    + Body

            {
                "message": "User could not be found."
            }

## Create new user [POST /users]
Create a new user.

+ Request (application/json)
    + Body

            {
                "name": "jason",
                "email": "jason@jason.com",
                "password": "1234567"
            }

+ Response 200 (application/json)
    + Body

            {
                "id": 10,
                "name": "jason",
                "email": "jason@jason.com"
            }

+ Response 422 (application/json)
    + Body

            {
                "message": "Unable to create user due to validation errors.",
                "errors": {
                    "name": [
                        "The name already exists."
                    ]
                }
            }
EOT;

        $this->assertEquals(trim($expected), $blueprint->generate($resources, 'testing', 'v1'));
    }
}
