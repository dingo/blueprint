<?php

namespace Dingo\Blueprint\Tests\Stubs;

/**
 * User Photos Resource.
 *
 * @Resource("User Photos", uri="/users/{userId}/photos")
 * @Parameters({
 *      @Parameter("userId", description="ID of user who owns the photos.", type="integer", required=true)
 * })
 */
class UserPhotosResourceStub
{
    /**
     * Show all photos.
     *
     * Show all photos for a given user.
     *
     * @Get("/{?sort,order}")
     * @Parameters({
     *      @Parameter("sort", description="Column to sort by.", type="string", default="name"),
     *      @Parameter("order", description="Order of results, either `asc` or `desc`.", type="string", default="desc", members={
     *          @Member("asc", description="Ascending order."),
     *          @Member("desc", description="Descending order."),
     *      })
     * })
     * @Versions({"v1", "v2"})
     * @Response(200, body={
     *      {"id": 1, "name": "photo", "src": "path/to/cool/photo.jpg"}
     * })
     */
    public function index($userId)
    {
        //
    }

    /**
     * Show individual photo.
     *
     * Show an individual photo that belongs to a given user.
     *
     * @Get("/{photoId}")
     * @Versions({"v2"})
     * @Parameters({
     *      @Parameter("photoId", description="ID of photo to show.", type="integer", required=true)
     * })
     * @Transaction({
     *      @Response(200, body={
     *          "id": 1,
     *          "name": "photo",
     *          "src": "path/to/cool/photo.jpg"
     *      }, attributes={
     *          @Attribute("id", type="number", description="The photo id", sample="1"),
     *          @Attribute("name", type="string", description="The photo name", sample="photo"),
     *          @Attribute("src", type="string", description="The photo path", sample="path/to/cool/photo.jpg")
     *      }),
     *      @Response(404, body={"message": "Photo could not be found."})
     * })
     */
    public function show($userId, $photoId)
    {
        //
    }

    /**
     * Upload new photo.
     *
     * Upload a new photo for a given user.
     *
     * @Post("/")
     * @Versions({"v1", "v2"})
     * @Attributes({
     *      @Attribute("name", type="string", description="The name of the photo", sample="photo"),
     *      @Attribute("src", type="string", description="The location of the photo", required=true)
     * })
     * @Transaction({
     *      @Request({"name": "photo", "src": "cool/new/photo.jpg"}),
     *      @Response(201, body={
     *          "id": 2,
     *          "name": "photo",
     *          "src": "cool/new/photo.jpg"
     *      }),
     *      @Response(422, body={
     *          "message": "Could not upload photo due to errors."
     *      })
     * })
     */
    public function store($userId)
    {
        //
    }

    /**
     * Delete photo.
     *
     * Delete an existing photo for a given user.
     *
     * @Delete("/{photoId}")
     * @Versions({"v2"})
     * @Parameters({
     *      @Parameter("photoId", description="ID of photo to delete.", type="integer", required=true)
     * })
     * @Transaction({
     *      @Response(204),
     *      @Response(400, body={
     *          "message": "Could not delete photo."
     *      })
     * })
     */
    public function destroy($userId, $photoId)
    {
        //
    }
}
