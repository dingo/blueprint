<?php

namespace Dingo\Blueprint\Tests;

use Dingo\Blueprint\Blueprint;
use PHPUnit_Framework_TestCase;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Doctrine\Common\Annotations\SimpleAnnotationReader;

class BlueprintTest extends PHPUnit_Framework_TestCase
{
    public function testGeneratingBlueprintForSingleResource()
    {
        $resources = new Collection([new Stubs\UsersResourceStub]);

        $blueprint = new Blueprint(new SimpleAnnotationReader, new Filesystem);

        $expected = <<<'EOT'
FORMAT: 1A

# testing

# Users [/users]
Users Resource.

## Show all users. [GET /users]
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

## Show existing user. [GET /users/{id}]
Get a JSON representation of an existing user.

+ Parameters
    + id: `5` (integer, required) - ID of user to retrieve

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

## Create new user. [POST /users]
Create a new user.

+ Request (application/json)

    + Attributes
        + name: jason (string, required) - The user name
        + email: jason@jason.com (string, required) - The user email
        + password: 1234567 (string, required) - The user password
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

        $this->assertEquals(trim($expected), $blueprint->generate($resources, 'testing', 'v1', null));
    }

    public function testGeneratingBlueprintForMultipleResourcesWithVersionOne()
    {
        $resources = new Collection([new Stubs\UsersResourceStub, new Stubs\UserPhotosResourceStub]);

        $blueprint = new Blueprint(new SimpleAnnotationReader, new Filesystem);

        $expected = <<<'EOT'
FORMAT: 1A

# testing

# Users [/users]
Users Resource.

## Show all users. [GET /users]
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

## Show existing user. [GET /users/{id}]
Get a JSON representation of an existing user.

+ Parameters
    + id: `5` (integer, required) - ID of user to retrieve

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

## Create new user. [POST /users]
Create a new user.

+ Request (application/json)

    + Attributes
        + name: jason (string, required) - The user name
        + email: jason@jason.com (string, required) - The user email
        + password: 1234567 (string, required) - The user password
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
User Photos Resource.

+ Parameters
    + userId: (integer, required) - ID of user who owns the photos.

## Show all photos. [GET /users/{userId}/photos{?sort,order}]
Show all photos for a given user.

+ Parameters
    + sort: (string, optional) - Column to sort by.
        + Default: name
    + order: (enum[string], optional) - Order of results, either `asc` or `desc`.
        + Default: desc
        + Members
            + `asc` - Ascending order.
            + `desc` - Descending order.

+ Response 200 (application/json)
    + Body

            [
                {
                    "id": 1,
                    "name": "photo",
                    "src": "path/to/cool/photo.jpg"
                }
            ]

## Upload new photo. [POST /users/{userId}/photos]
Upload a new photo for a given user.

+ Attributes
    + name: photo (string, optional) - The name of the photo
    + src (string, required) - The location of the photo

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

        $this->assertEquals(trim($expected), $blueprint->generate($resources, 'testing', 'v1', null));
    }

    public function testGeneratingBlueprintForMultipleResourcesWithVersionTwo()
    {
        $resources = new Collection([new Stubs\UsersResourceStub, new Stubs\UserPhotosResourceStub]);

        $blueprint = new Blueprint(new SimpleAnnotationReader, new Filesystem);

        $expected = <<<'EOT'
FORMAT: 1A

# testing

# Users [/users]
Users Resource.

## Show all users. [GET /users]
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

## Show existing user. [GET /users/{id}]
Get a JSON representation of an existing user.

+ Parameters
    + id: `5` (integer, required) - ID of user to retrieve

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

## Create new user. [POST /users]
Create a new user.

+ Request (application/json)

    + Attributes
        + name: jason (string, required) - The user name
        + email: jason@jason.com (string, required) - The user email
        + password: 1234567 (string, required) - The user password
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
User Photos Resource.

+ Parameters
    + userId: (integer, required) - ID of user who owns the photos.

## Show all photos. [GET /users/{userId}/photos{?sort,order}]
Show all photos for a given user.

+ Parameters
    + sort: (string, optional) - Column to sort by.
        + Default: name
    + order: (enum[string], optional) - Order of results, either `asc` or `desc`.
        + Default: desc
        + Members
            + `asc` - Ascending order.
            + `desc` - Descending order.

+ Response 200 (application/json)
    + Body

            [
                {
                    "id": 1,
                    "name": "photo",
                    "src": "path/to/cool/photo.jpg"
                }
            ]

## Show individual photo. [GET /users/{userId}/photos/{photoId}]
Show an individual photo that belongs to a given user.

+ Parameters
    + photoId: (integer, required) - ID of photo to show.

+ Response 200 (application/json)

    + Attributes
        + id: 1 (number, optional) - The photo id
        + name: photo (string, optional) - The photo name
        + src: path/to/cool/photo.jpg (string, optional) - The photo path
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

## Upload new photo. [POST /users/{userId}/photos]
Upload a new photo for a given user.

+ Attributes
    + name: photo (string, optional) - The name of the photo
    + src (string, required) - The location of the photo

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

## Delete photo. [DELETE /users/{userId}/photos/{photoId}]
Delete an existing photo for a given user.

+ Parameters
    + photoId: (integer, required) - ID of photo to delete.

+ Response 204 (application/json)

+ Response 400 (application/json)
    + Body

            {
                "message": "Could not delete photo."
            }
EOT;

        $this->assertEquals(trim($expected), $blueprint->generate($resources, 'testing', 'v2', null));
    }

    public function testGeneratingSimpleBlueprints()
    {
        $resources = new Collection([new Stubs\ActivityController]);

        $blueprint = new Blueprint(new SimpleAnnotationReader, new Filesystem);

        $expected = <<<'EOT'
FORMAT: 1A

# testing

# Activity

## Show all activities. [GET /activity]
EOT;

        $this->assertEquals(trim($expected), $blueprint->generate($resources, 'testing', 'v1', null));
    }

    public function testGeneratingBlueprintOverview()
    {
        $resources = new Collection([new Stubs\ActivityController]);

        $blueprint = new Blueprint(new SimpleAnnotationReader, new Filesystem);

        $expected = <<<'EOT'
FORMAT: 1A

# testing

Overview content here.

# Activity

## Show all activities. [GET /activity]
EOT;

        $this->assertEquals(trim($expected), $blueprint->generate($resources, 'testing', 'v1', null, __DIR__.'/Files/overview.apib'));


    }
}
