<?php
use Illuminate\Console\Command;
use App\Models\Book;
use App\Models\OrderItem;

class UpdateBestsellers extends Command
{
    protected $signature = 'bestsellers:update';
    protected $description = 'Update bestseller books based on sales data';

    public function handle()
    {
        // Calculate sales count for each book
        $salesData = OrderItem::selectRaw('books_id, SUM(quantity) as total_sales')
            ->groupBy('books_id')
            ->get();

        foreach ($salesData as $data) {
            Book::where('id', $data->books_id)->update(['sales_count' => $data->total_sales]);
        }

        $this->info('Bestseller books updated successfully!');
    }
}
