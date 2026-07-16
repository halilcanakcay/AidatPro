<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Expense;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $sites = Site::query()->orderBy('name')->get();
        $site = Site::query()->find($request->query('site_id')) ?? $sites->firstOrFail();
        $year = (int) $request->query('year', 2026);
        $month = $request->filled('month') ? max(1, min(12, (int) $request->query('month'))) : null;
        $category = (string) $request->query('category', '');
        $search = trim((string) $request->query('search', ''));

        $expenses = Expense::query()
            ->where('site_id', $site->id)
            ->whereYear('expense_date', $year)
            ->when($month, fn ($query) => $query->whereMonth('expense_date', $month))
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('category', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest('expense_date')
            ->paginate(12)
            ->withQueryString();

        return view('expenses.index', [
            'site' => $site,
            'sites' => $sites,
            'expenses' => $expenses,
            'months' => $this->months(),
            'categories' => $this->categories(),
            'year' => $year,
            'month' => $month,
            'category' => $category,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('expenses.create', [
            'site' => Site::query()->firstOrFail(),
            'categories' => $this->categories(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
        ]);

        $site = Site::query()->firstOrFail();
        $expense = Expense::create($validated + ['site_id' => $site->id]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'create_expense',
            'table_name' => 'expenses',
            'record_id' => $expense->id,
            'ip_address' => $request->ip(),
            'description' => "{$expense->category} gideri kaydedildi.",
        ]);

        return redirect()->route('expenses.index')->with('status', 'Gider kaydedildi.');
    }

    public function edit(Expense $expense): View
    {
        return view('expenses.edit', [
            'expense' => $expense,
            'categories' => $this->categories(),
        ]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
        ]);

        $expense->update($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'update_expense',
            'table_name' => 'expenses',
            'record_id' => $expense->id,
            'ip_address' => $request->ip(),
            'description' => "{$expense->category} gideri güncellendi.",
        ]);

        return redirect()->route('expenses.index')->with('status', 'Gider güncellendi.');
    }

    public function destroy(Request $request, Expense $expense): RedirectResponse
    {
        $description = $expense->description;
        $expense->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'delete_expense',
            'table_name' => 'expenses',
            'ip_address' => $request->ip(),
            'description' => "{$description} gideri silindi.",
        ]);

        return redirect()->route('expenses.index')->with('status', 'Gider silindi.');
    }

    private function categories(): array
    {
        return ['Bakım', 'Temizlik', 'Güvenlik', 'Elektrik', 'Su', 'Personel', 'Diğer'];
    }

    private function months(): array
    {
        return [
            1 => 'Ocak',
            2 => 'Şubat',
            3 => 'Mart',
            4 => 'Nisan',
            5 => 'Mayıs',
            6 => 'Haziran',
            7 => 'Temmuz',
            8 => 'Ağustos',
            9 => 'Eylül',
            10 => 'Ekim',
            11 => 'Kasım',
            12 => 'Aralık',
        ];
    }
}
