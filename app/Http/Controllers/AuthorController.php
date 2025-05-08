<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class AuthorController extends Controller
{
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|email|unique:authors,email',
    //         'description' => 'nullable|string',
    //         'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // Validate image
    //     ]);

    //     $author = new Author();
    //     $author->name = $request->name;
    //     $author->email = $request->email;
    //     $author->description = $request->description;

    //     if ($request->hasFile('image')) {
    //         $imagePath = $request->file('image')->store('authors', 'public');
    //         $author->image = $imagePath;
    //     }
    //     $author->save();

    //     return response()->json(['message' => 'Author created successfully!', 'author' => $author], 201);
    // }
    public function store(Request $request)
    {
        // Validate request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:authors,email',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Configure Cloudinary
        Configuration::instance([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
        ]);

        // Create a new author
        $author = new Author();
        $author->name = $request->name;
        $author->email = $request->email;
        $author->description = $request->description;

        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');

            // Use the Upload API to upload the image to Cloudinary
            $uploadApi = new UploadApi();
            $uploadedFile = $uploadApi->upload(
                $imageFile->getRealPath(),
                [
                    'folder' => 'imageAuthors', // Specify the folder
                ]
            );

            // Get the URL of the uploaded image
            $imageUrl = $uploadedFile['secure_url'];

            // Save the image URL in the database
            $author->image = $imageUrl;
        }

        // Save the author
        $author->save();

        // Format and return the response
        return response()->json([
            'message' => 'Author created successfully!',
            'author' => [
                'id' => $author->id,
                'name' => $author->name,
                'email' => $author->email,
                'description' => $author->description,
                'image' => $author->image,
                'created_at' => $author->created_at->toIso8601String(),
                'updated_at' => $author->updated_at->toIso8601String(),
            ],
        ], 201);
    }

    // public function storex(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|email|unique:authors,email',
    //         'description' => 'nullable|string',
    //         'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // Validate image
    //     ]);

    //     // Cloudinary configuration
    //     Configuration::instance([
    //         'cloud' => [
    //             'cloud_name' => env('Bookmanagement'),
    //             'api_key' => env('843717747511777'),
    //             'api_secret' => env('ypTArLdjrwO1f4qm-g2b3ebF2Mo'),
    //         ],
    //     ]);

    //     $author = new Author();
    //     $author->name = $request->name;
    //     $author->email = $request->email;
    //     $author->description = $request->description;

    //     if ($request->hasFile('image')) {
    //         $imageFile = $request->file('image');

    //         // Upload to Cloudinary
    //         $cloudinary = new Cloudinary();
    //         $uploadedFile = $cloudinary->uploadApi()->upload($imageFile->getRealPath());

    //         // Get the Cloudinary URL
    //         $imageUrl = $uploadedFile['secure_url'];

    //         $author->image = $imageUrl;
    //     }

    //     $author->save();

    //     return response()->json(['message' => 'Author created successfully!', 'author' => $author], 201);
    // }

    public function getAllAuthors()
    {
        $authors = Author::all();

        // Return response with the list of authors
        return response()->json([
            'message' => 'Authors retrieved successfully!',
            'authors' => $authors->map(function ($author) {
                return [
                    'id' => $author->id,
                    'name' => $author->name,
                    'email' => $author->email,
                    'description' => $author->description,
                    'image' => $author->image,
                    'created_at' => $author->created_at->toIso8601String(),
                    'updated_at' => $author->updated_at->toIso8601String(),
                ];
            }),
        ], 200);
    }
    public function show($id)
    {
        $author = Author::find($id);

        if (!$author) {
            return response()->json(['message' => 'Author not found'], 404);
        }

        // Return response with the author's data
        return response()->json([
            'message' => 'Author retrieved successfully!',
            'author' => [
                'id' => $author->id,
                'name' => $author->name,
                'email' => $author->email,
                'description' => $author->description,
                'image' => $author->image,
                'created_at' => $author->created_at->toIso8601String(),
                'updated_at' => $author->updated_at->toIso8601String(),
            ],
        ], 200);
    }



    public function specific_author($id)
    {
        // Retrieve the author with their books
        $author = Author::with('books')->find($id);

        if (!$author) {
            return response()->json(['message' => 'Author not found'], 404);
        }

        return response()->json([
            'message' => 'Author retrieved successfully!',
            'author' => $author,
            'books' => $author->book,  // Include the author's books in the response
        ], 200);
    }
    public function update(Request $request, $id)
    {
        // Validate incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:authors,email,' . $id,
            'description' => 'nullable|string',
           'image' => 'nullable|image|mimes:jpg,jpeg,png|max:20248',

        ]);
        Configuration::instance([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
        ]);
    
        // Fetch the author to be updated
        $author = Author::find($id);
    
        if (!$author) {
            return response()->json(['message' => 'Author not found'], 404);
        }
    
        // Update author fields
        $author->name = $request->name;
        $author->email = $request->email;
        $author->description = $request->description;
    
        // Only handle image upload if a new image is provided
        if ($request->hasFile('image')) {
            // Configure Cloudinary upload
            $uploadApi = new UploadApi();
    
            // If the author already has an image, delete the old one
            if (!empty($author->image)) {
                $oldImageUrl = $author->image;
                $imagePublicId = pathinfo(parse_url($oldImageUrl, PHP_URL_PATH), PATHINFO_FILENAME);
                if ($imagePublicId) {
                    $uploadApi->destroy($imagePublicId); // Delete the old image from Cloudinary
                }
            }
    
            // Upload the new image to Cloudinary
            $uploadedFile = $uploadApi->upload($request->file('image')->getRealPath(), [
                'folder' => 'imageAuthors',
            ]);
    
            // Store the new image URL
            $author->image = $uploadedFile['secure_url'];
        }
    
        // Save the updated author data
        $author->save();
    
        // Return response with updated author data
        return response()->json([
            'message' => 'Author updated successfully!',
            'author' => [
                'id' => $author->id,
                'name' => $author->name,
                'email' => $author->email,
                'description' => $author->description,
                'image' => $author->image,
                'created_at' => $author->created_at->toIso8601String(),
                'updated_at' => $author->updated_at->toIso8601String(),
            ],
        ], 200);
    }
    public function destroy($id)
    {
        $author = Author::find($id);

        if (!$author) {
            return response()->json(['message' => 'Author not found'], 404);
        }
        Configuration::instance([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
        ]);

        $uploadApi = new \Cloudinary\Api\Upload\UploadApi();
        if ($author->image) {
            $imageUrl = $author->image;
            $imagePublicId = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_FILENAME);
            $imagePublicIdWithFolder = 'imageAuthors/' . $imagePublicId;
            try {
                $uploadApi->destroy($imagePublicIdWithFolder);
            } catch (\Exception $e) {
                // Log the error or handle it as needed
                return response()->json(['message' => 'Failed to delete image from Cloudinary', 'error' => $e->getMessage()], 500);
            }
        }

        // Delete the author record
        $author->delete();

        return response()->json(['message' => 'Author deleted successfully!']);
    }
}
