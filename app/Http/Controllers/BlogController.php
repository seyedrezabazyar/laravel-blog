<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Author;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::where('is_published', true)
            ->where('is_restricted', false)
            ->with(['category', 'authors'])
            ->latest()
            ->paginate(12);

        return view('books.index', compact('books'));
    }

    public function show(Book $book)
    {
        // اگر کتاب مخفی است و کاربر مدیر نیست، 404 برگردان
        if ($book->is_restricted && (!auth()->check() || !auth()->user()->isAdmin())) {
            abort(404);
        }

        // اگر کتاب منتشر نشده است و کاربر مدیر نیست، 404 برگردان
        if (!$book->is_published && (!auth()->check() || !auth()->user()->isAdmin())) {
            abort(404);
        }

        $book->load('authors', 'category');
        $relatedBooks = Book::where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->where('is_published', true)
            ->where('is_restricted', false)
            ->take(4)
            ->get();

        return view('books.show', compact('book', 'relatedBooks'));
    }

    public function category(Category $category)
    {
        $books = Book::where('category_id', $category->id)
            ->where('is_published', true)
            ->where('is_restricted', false)
            ->with(['authors', 'category'])
            ->latest()
            ->paginate(12);

        return view('books.category', compact('books', 'category'));
    }

    public function author(Author $author)
    {
        $books = $author->publicBooks()->latest()->paginate(12);
        return view('books.author', compact('books', 'author'));
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        $books = Book::where('is_published', true)
            ->where('is_restricted', false)
            ->where(function($q) use ($query) {
                $q->where('title_fa', 'like', "%{$query}%")
                    ->orWhere('title_en', 'like', "%{$query}%")
                    ->orWhere('description_fa', 'like', "%{$query}%")
                    ->orWhere('description_en', 'like', "%{$query}%")
                    ->orWhere('keywords', 'like', "%{$query}%")
                    ->orWhere('isbn_codes', 'like', "%{$query}%");
            })
            ->with(['category', 'authors'])
            ->latest()
            ->paginate(12);

        return view('books.search', compact('books', 'query'));
    }
}
