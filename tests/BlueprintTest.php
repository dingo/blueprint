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

# Group Accounts

# Users [/users]
Users Resource

## Show all users [GET /users]
Get a JSON representation of all registered users.

+ Request (application/json)
    + Headers

            Accept: application/vnd.api.v1+json

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

    public function testGeneratingBlueprintForMultipleResourcesWithVersionOne()
    {
        $resources = new Collection([new Stubs\UsersResourceStub, new Stubs\UserPhotosResourceStub]);

        $blueprint = new Blueprint(new SimpleAnnotationReader);

        $expected = <<<EOT
FORMAT: 1A

# testing

# Group Accounts

# Users [/users]
Users Resource

## Show all users [GET /users]
Get a JSON representation of all registered users.

+ Request (application/json)
    + Headers

            Accept: application/vnd.api.v1+json

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

# User Photos [/users/{userId}/photos]
User Photos Resource

+ Parameters
    + userId (integer, required) - ID of user who owns the photos.

## Show all photos [GET /users/{userId}/photos{?sort,order}]
Show all photos for a given user.

+ Parameters
    + sort (string, optional) - Column to sort by.
        + Default: name
    + order (string, optional) - Order of results, either `asc` or `desc`.
        + Default: desc

+ Response 200 (application/json)
    + Body

            [
                {
                    "id": 1,
                    "name": "photo",
                    "src": "path/to/cool/photo.jpg"
                }
            ]

## Upload new photo [POST /users/{userId}/photos]
Upload a new photo for a given user.

+ Request (application/json)
    + Body

            {
                "name": "photo",
                "src": "cool/new/photo.jpg"
            }

+ Response 201 (application/json)
    + Body

            {
                "id": 2,
                "name": "photo",
                "src": "cool/new/photo.jpg"
            }

+ Response 422 (application/json)
    + Body

            {
                "message": "Could not upload photo due to errors."
            }
EOT;

        $this->assertEquals(trim($expected), $blueprint->generate($resources, 'testing', 'v1'));
    }

    public function testGeneratingBlueprintForMultipleResourcesWithVersionTwo()
    {
        $resources = new Collection([new Stubs\UsersResourceStub, new Stubs\UserPhotosResourceStub]);

        $blueprint = new Blueprint(new SimpleAnnotationReader);

        $expected = <<<EOT
FORMAT: 1A

# testing

# Group Accounts

# Users [/users]
Users Resource

## Show all users [GET /users]
Get a JSON representation of all registered users.

+ Request (application/json)
    + Headers

            Accept: application/vnd.api.v1+json

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

# User Photos [/users/{userId}/photos]
User Photos Resource

+ Parameters
    + userId (integer, required) - ID of user who owns the photos.

## Show all photos [GET /users/{userId}/photos{?sort,order}]
Show all photos for a given user.

+ Parameters
    + sort (string, optional) - Column to sort by.
        + Default: name
    + order (string, optional) - Order of results, either `asc` or `desc`.
        + Default: desc

+ Response 200 (application/json)
    + Body

            [
                {
                    "id": 1,
                    "name": "photo",
                    "src": "path/to/cool/photo.jpg"
                }
            ]

## Show individual photo [GET /users/{userId}/photos/{photoId}]
Show an individual photo that belongs to a given user.

+ Parameters
    + photoId (integer, required) - ID of photo to show.

+ Response 200 (application/json)
    + Body

            {
                "id": 1,
                "name": "photo",
                "src": "path/to/cool/photo.jpg"
            }

+ Response 404 (application/json)
    + Body

            {
                "message": "Photo could not be found."
            }

## Upload new photo [POST /users/{userId}/photos]
Upload a new photo for a given user.

+ Request (application/json)
    + Body

            {
                "name": "photo",
                "src": "cool/new/photo.jpg"
            }

+ Response 201 (application/json)
    + Body

            {
                "id": 2,
                "name": "photo",
                "src": "cool/new/photo.jpg"
            }

+ Response 422 (application/json)
    + Body

            {
                "message": "Could not upload photo due to errors."
            }

## Delete photo [DELETE /users/{userId}/photos/{photoId}]
Delete an existing photo for a given user.

+ Parameters
    + photoId (integer, required) - ID of photo to delete.

+ Response 204 (application/json)

+ Response 400 (application/json)
    + Body

            {
                "message": "Could not delete photo."
            }
EOT;

        $this->assertEquals(trim($expected), $blueprint->generate($resources, 'testing', 'v2'));
    }
}
