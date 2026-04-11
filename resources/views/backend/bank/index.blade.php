@extends('admin_dashboard')
@section('admin')

<style>
    .bank-card {
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        border: none;
        overflow: hidden;
    }

    .balance-display {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        text-align: center;
        margin-bottom: 20px;
    }

    .balance-display-iqd {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        text-align: center;
        margin-bottom: 20px;
    }

    .balance-display h2,
    .balance-display-iqd h2 {
        font-size: 2.5rem;
        margin-bottom: 10px;
    }

    .balance-display p,
    .balance-display-iqd p {
        font-size: 1.1rem;
        opacity: 0.9;
    }

    .form-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .form-section h5 {
        color: #333;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .spend-section {
        border-left: 5px solid #dc3545;
    }

    .receive-section {
        border-left: 5px solid #28a745;
    }

    .form-label {
        font-weight: 600;
        color: #555;
        margin-bottom: 8px;
    }

    .btn-spend {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border: none;
        color: white;
        font-weight: 600;
    }

    .btn-spend:hover {
        background: linear-gradient(135deg, #c82333 0%, #a71d2a 100%);
        color: white;
    }

    .btn-receive {
        background: linear-gradient(135deg, #28a745 0%, #218838 100%);
        border: none;
        color: white;
        font-weight: 600;
    }

    .btn-receive:hover {
        background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
        color: white;
    }

    .summary-box {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .summary-card {
        flex: 1;
        min-width: 200px;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        color: white;
    }

    .summary-card h6 {
        margin-bottom: 10px;
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .summary-card .amount {
        font-size: 1.8rem;
        font-weight: 700;
    }

    .spend-card {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }

    .receive-card {
        background: linear-gradient(135deg, #28a745 0%, #218838 100%);
    }

    .balance-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .balance-card-iqd {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    }

    .transaction-table {
        margin-top: 30px;
    }

    .table-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .table-header-iqd {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
    }

    .badge-spend {
        background: #dc3545;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 0.85rem;
    }

    .badge-receive {
        background: #28a745;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 0.85rem;
    }

    .filter-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .filter-section h6 {
        color: white;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .filter-section input {
        border-radius: 8px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        padding: 10px 12px;
        background: white;
        color: #333;
    }

    .filter-section .btn {
        background: white;
        color: #667eea;
        border: none;
        font-weight: 600;
    }

    .filter-section .btn:hover {
        background: #f0f0f0;
    }

    .currency-divider {
        border-top: 3px dashed #ddd;
        margin: 40px 0;
        padding-top: 20px;
    }

    .currency-header {
        font-size: 1.3rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 5px solid #e74c3c;
    }
</style>

<div class="content">
    <div class="container-fluid">

        <!-- PAGE TITLE -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title" style="font-weight: 600; color: #2c3e50;">
                        <i class="mdi mdi-bank me-2"></i> بانکی فرۆشگا
                    </h4>
                    <p class="text-muted mb-0">بەڕێوەبردنی پارەی بانکی (USD و IQD)</p>
                </div>
            </div>
        </div>

        <!-- ======================== USD SECTION ======================== -->

        <!-- USD INITIAL SETUP / CURRENT BALANCE DISPLAY -->
        <div class="row mb-4">
            <div class="col-12">
                @if($currentBalance == 0 && \App\Models\BankTransaction::where('currency', 'USD')->count() == 0)
                <!-- FIRST TIME SETUP -->
                <div class="card bank-card">
                    <div class="card-body">
                        <h5 class="mb-3"><i class="mdi mdi-alert-circle me-2"></i>سەتوپکردنی بانک (دۆلار)</h5>
                        <p class="text-muted">تکایە ابتدائی سەرمایە دۆلار داخڵ بکە:</p>
                        
                        <form action="{{ route('bank.receive') }}" method="POST" class="d-flex gap-2">
                            @csrf
                            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" 
                                   placeholder="سەرمایە سەرەتایی (دۆلار)..." style="max-width: 300px;" required>
                            <input type="hidden" name="description" value="سەرمایە سەرەتایی دۆلار">
                            <button type="submit" class="btn btn-receive">
                                <i class="mdi mdi-check me-1"></i>کۆنفرم کردن
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <!-- BALANCE DISPLAY AFTER SETUP -->
                <div class="balance-display">
                    <p>کۆی سەرمایەی بانک - دۆلار (ئەمڕۆ)</p>
                    <h2>${{ number_format($currentBalance, 2) }}</h2>
                </div>
                @endif
            </div>
        </div>

        <!-- USD TODAY'S SUMMARY -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="summary-box">
                    <div class="summary-card spend-card">
                        <h6>دۆلار کەم کراو ئەمڕۆ</h6>
                        <div class="amount">${{ number_format($todaySpend, 2) }}</div>
                    </div>
                    <div class="summary-card receive-card">
                        <h6>دۆلار دەست کەوتۆو ئەمڕۆ</h6>
                        <div class="amount">${{ number_format($todayReceive, 2) }}</div>
                    </div>
                    <div class="summary-card balance-card">
                        <h6>بەروار: {{ \Carbon\Carbon::parse($selectedDate)->format('d-m-Y') }}</h6>
                        <div class="amount">${{ number_format($balanceOnDate, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DATE FILTER -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="filter-section">
                    <h6><i class="mdi mdi-calendar-range"></i> بەروار هەڵبژێرە</h6>
                    <form method="GET" action="{{ route('bank.index') }}" class="d-flex gap-2">
                        <input type="date" name="date" class="form-control" value="{{ $selectedDate }}" style="max-width: 200px;">
                        <button type="submit" class="btn">🔍 گەڕان</button>
                        <a href="{{ route('bank.index') }}" class="btn btn-secondary">🔄 لابردن</a>
                    </form>
                </div>
            </div>
        </div>

        <!-- USD FORMS SECTION -->
        <div class="row mb-4">
            <!-- SPEND MONEY FORM -->
            <div class="col-lg-6">
                <div class="card bank-card">
                    <div class="card-body">
                        <div class="form-section spend-section">
                            <h5><i class="mdi mdi-cash-remove me-2"></i>دۆلار بەرداشت کردن</h5>
                            <form action="{{ route('bank.spend') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">بڕی دۆلار</label>
                                    <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" 
                                           step="0.01" min="0.01" placeholder="0.00" required>
                                    @error('amount')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">وەسف (بۆچی/کێ)</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                              rows="3" placeholder="نووسین... مثال: رێت، ژینگە، بیانی..." required></textarea>
                                    @error('description')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-spend w-100">
                                    <i class="mdi mdi-check-circle me-1"></i> دۆلار بەرداشت بکە
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RECEIVE MONEY FORM -->
            <div class="col-lg-6">
                <div class="card bank-card">
                    <div class="card-body">
                        <div class="form-section receive-section">
                            <h5><i class="mdi mdi-cash-plus me-2"></i>دۆلار دەست کەوتن</h5>
                            <form action="{{ route('bank.receive') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">بڕی دۆلار</label>
                                    <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" 
                                           step="0.01" min="0.01" placeholder="0.00" required>
                                    @error('amount')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">وەسف (لە کێ/کێ)</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                              rows="3" placeholder="نووسین... مثال: فرۆش، کڕیار، قەرز..." required></textarea>
                                    @error('description')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-receive w-100">
                                    <i class="mdi mdi-check-circle me-1"></i> دۆلار زیاد بکە
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- USD TRANSACTIONS TABLE -->
        <div class="row">
            <div class="col-12">
                <div class="card bank-card transaction-table">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <i class="mdi mdi-history me-2"></i>
                            مێژووی لیکدان و دەست کەوتنی دۆلار - {{ \Carbon\Carbon::parse($selectedDate)->format('d-m-Y') }}
                        </h5>

                        @if($transactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-header">
                                    <tr>
                                        <th>#</th>
                                        <th>جۆری لیکدان</th>
                                        <th>بڕ</th>
                                        <th>وەسف</th>
                                        <th>بەروار/کات</th>
                                        <th>سەرمایە دوای لیکدان</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sl = 1; @endphp
                                    @foreach($transactions as $transaction)
                                    <tr>
                                        <td>{{ $sl++ }}</td>
                                        <td>
                                            @if($transaction->transaction_type == 'spend')
                                                <span class="badge-spend">بەرداشت</span>
                                            @else
                                                <span class="badge-receive">دەست کەوتن</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text-{{ $transaction->transaction_type == 'spend' ? 'danger' : 'success' }}">
                                                {{ $transaction->transaction_type == 'spend' ? '-' : '+' }}${{ number_format($transaction->amount, 2) }}
                                            </strong>
                                        </td>
                                        <td>
                                            <small>{{ $transaction->description }}</small>
                                        </td>
                                        <td>
                                            <small>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d-m-Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <strong class="text-info">${{ number_format($transaction->balance_after, 2) }}</strong>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-info text-center" role="alert">
                            <i class="mdi mdi-information-outline me-2"></i>
                            هیچ لیکدانی دۆلاری بۆ ئەم بەرواری نیە
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================== IQD SECTION ======================== -->
        <div class="currency-divider">
            <div class="currency-header">
                <i class="mdi mdi-currency-jpy me-2"></i> سیستەمی دینار عێراقی (IQD)
            </div>
        </div>

        <!-- IQD INITIAL SETUP / CURRENT BALANCE DISPLAY -->
        <div class="row mb-4">
            <div class="col-12">
                @if($currentBalanceIQD == 0 && \App\Models\BankTransaction::where('currency', 'IQD')->count() == 0)
                <!-- IQD FIRST TIME SETUP -->
                <div class="card bank-card">
                    <div class="card-body">
                        <h5 class="mb-3"><i class="mdi mdi-alert-circle me-2"></i>سەتوپکردنی بانکی دینار</h5>
                        <p class="text-muted">تکایە ابتدائی سەرمایەی دینار داخڵ بکە:</p>
                        
                        <form action="{{ route('bank.receive.iqd') }}" method="POST" class="d-flex gap-2">
                            @csrf
                            <input type="number" name="amount" class="form-control" step="1" min="1" 
                                   placeholder="سەرمایە سەرەتایی (دینار)..." style="max-width: 300px;" required>
                            <input type="hidden" name="description" value="سەرمایە سەرەتایی دینار">
                            <button type="submit" class="btn btn-receive">
                                <i class="mdi mdi-check me-1"></i>کۆنفرم کردن
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <!-- IQD BALANCE DISPLAY AFTER SETUP -->
                <div class="balance-display-iqd">
                    <p>کۆی سەرمایەی بانک - دینار (ئەمڕۆ)</p>
                    <h2>{{ number_format($currentBalanceIQD, 0) }} IQD</h2>
                </div>
                @endif
            </div>
        </div>

        <!-- IQD TODAY'S SUMMARY -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="summary-box">
                    <div class="summary-card" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                        <h6>دینار کەم کراو ئەمڕۆ</h6>
                        <div class="amount">{{ number_format($todaySpendIQD, 0) }} IQD</div>
                    </div>
                    <div class="summary-card" style="background: linear-gradient(135deg, #28a745 0%, #218838 100%);">
                        <h6>دینار دەست کەوتۆو ئەمڕۆ</h6>
                        <div class="amount">{{ number_format($todayReceiveIQD, 0) }} IQD</div>
                    </div>
                    <div class="summary-card balance-card-iqd">
                        <h6>بەروار: {{ \Carbon\Carbon::parse($selectedDate)->format('d-m-Y') }}</h6>
                        <div class="amount">{{ number_format($balanceOnDateIQD, 0) }} IQD</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- IQD FORMS SECTION -->
        <div class="row mb-4">
            <!-- SPEND IQD FORM -->
            <div class="col-lg-6">
                <div class="card bank-card">
                    <div class="card-body">
                        <div class="form-section spend-section">
                            <h5><i class="mdi mdi-cash-remove me-2"></i>دینار بەرداشت کردن</h5>
                            <form action="{{ route('bank.spend.iqd') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">بڕی دینار</label>
                                    <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" 
                                           step="1" min="1" placeholder="0" required>
                                    @error('amount')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">وەسف (بۆچی/کێ)</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                              rows="3" placeholder="نووسین... مثال: رێت، ژینگە، بیانی..." required></textarea>
                                    @error('description')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-spend w-100">
                                    <i class="mdi mdi-check-circle me-1"></i> دینار بەرداشت بکە
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RECEIVE IQD FORM -->
            <div class="col-lg-6">
                <div class="card bank-card">
                    <div class="card-body">
                        <div class="form-section receive-section">
                            <h5><i class="mdi mdi-cash-plus me-2"></i>دینار دەست کەوتن</h5>
                            <form action="{{ route('bank.receive.iqd') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">بڕی دینار</label>
                                    <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" 
                                           step="1" min="1" placeholder="0" required>
                                    @error('amount')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">وەسف (لە کێ/کێ)</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                              rows="3" placeholder="نووسین... مثال: فرۆش، کڕیار، قەرز..." required></textarea>
                                    @error('description')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-receive w-100">
                                    <i class="mdi mdi-check-circle me-1"></i> دینار زیاد بکە
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- IQD TRANSACTIONS TABLE -->
        <div class="row">
            <div class="col-12">
                <div class="card bank-card transaction-table">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <i class="mdi mdi-history me-2"></i>
                            مێژووی لیکدان و دەست کەوتنی دینار - {{ \Carbon\Carbon::parse($selectedDate)->format('d-m-Y') }}
                        </h5>

                        @if($transactionsIQD->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-header-iqd">
                                    <tr>
                                        <th>#</th>
                                        <th>جۆری لیکدان</th>
                                        <th>بڕ</th>
                                        <th>وەسف</th>
                                        <th>بەروار/کات</th>
                                        <th>سەرمایە دوای لیکدان</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sl = 1; @endphp
                                    @foreach($transactionsIQD as $transaction)
                                    <tr>
                                        <td>{{ $sl++ }}</td>
                                        <td>
                                            @if($transaction->transaction_type == 'spend')
                                                <span class="badge-spend">بەرداشت</span>
                                            @else
                                                <span class="badge-receive">دەست کەوتن</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text-{{ $transaction->transaction_type == 'spend' ? 'danger' : 'success' }}">
                                                {{ $transaction->transaction_type == 'spend' ? '-' : '+' }}{{ number_format($transaction->amount, 0) }} IQD
                                            </strong>
                                        </td>
                                        <td>
                                            <small>{{ $transaction->description }}</small>
                                        </td>
                                        <td>
                                            <small>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d-m-Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <strong class="text-info">{{ number_format($transaction->balance_after, 0) }} IQD</strong>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-info text-center" role="alert">
                            <i class="mdi mdi-information-outline me-2"></i>
                            هیچ لیکدانی دینار بۆ ئەم بەرواری نیە
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- container -->
</div> <!-- content -->

@endsection