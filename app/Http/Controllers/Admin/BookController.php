<!-- 

namespace App\Http\Admin\Controllers;

use App\Models\Book;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller; 

class BookController extends Controller
{
    public function storeEbook(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'author_id' => 'required|exists:authors,id',
            'category_id' => 'required|exists:categories,id',
            'file' => 'required|file|mimes:pdf,epub,mobi|max:2048',
        ]);

        $path = $request->file('file')->store('ebooks', 'public');

        Book::create([
            'title' => $request->title,
            'author_id' => $request->author_id,
            'category_id' => $request->category_id,
            'file_path' => $path,
            'file_format' => $request->file->extension(),
            'file_size' => $request->file->getSize() / 1024, // Size in KB
            'type' => 'ebook',
        ]);

        return response()->json(['message' => 'E-Book added!'], 201);
    }

    public function createEbook()
    {
    }

    public function indexEbooks()
    {
        $ebooks = Book::where('type', 'ebook')->with(['author', 'category'])->get();
        return response()->json($ebooks);
    }

    public function storeHandbook(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'author_id' => 'required|exists:authors,id',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric',
        ]);

        Book::create([
            'title' => $request->title,
            'author_id' => $request->author_id,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'type' => 'handbook',
        ]);

        return response()->json(['message' => 'Handbook added!'], 201);
    }

    public function createHandbook()
    {
    }

    public function indexHandbooks()
    {
        $handbooks = Book::where('type', 'handbook')->with(['author', 'category'])->get();
        return response()->json($handbooks);
    }
} -->