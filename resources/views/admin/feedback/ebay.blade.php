@extends('admin.default')

@section('css')

@endsection

@section('page-header')

@endsection

@section('content')
    <div align="center">
        <img class="imgc" src="{{asset('images/ebay_logo.png')}}" alt="ebay's logo" width="200px">
        <br><br>
        <a href="{{ route('feed') }}"><b>Back to the Hub</b></a> |
        <a href="{{ route('ebay') }}"><b>Refresh Content</b></a> |
        <a href="{{ route('ebay') }}"><b>Sales</b></a>
    </div>
    <br><br>
    <div>
        <div class="masonry-item col-md-12">
            <!-- #Sales Report ==================== -->
            <div class="bd bgc-white">
                <div class="layers">
                    <div class="layer w-100 p-20">
                        <h6 class="lh-1">Sales Report</h6>
                    </div>
                    <div class="layer w-100">
                        <div class="bgc-light-blue-500 c-white p-20">
                            <div class="peers ai-c jc-sb gap-40">
                                <div class="peer peer-greed">
                                    <h5>Current Balance</h5>
                                    <p class="mB-0">Account State: {{ $response1->AccountSummary->AccountState }}</p>
                                </div>
                                <div class="peer">
                                    <h3 class="text-right">{{ $response1->AccountSummary->CurrentBalance->value }} {{ $response1->AccountSummary->CurrentBalance->currencyID }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive p-20">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th class=" bdwT-0">Name</th>
                                    <th class=" bdwT-0">Time Left</th>
                                    <th class=" bdwT-0">Start Date</th>
                                    <th class=" bdwT-0">Current Price</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php($i = 0)
                                @foreach($response2->ActiveList->ItemArray->Item as $item)
                                    <tr>
                                        <td class="fw-600"><a
                                                href="./eby/product/{{ $item->ItemID }}">{{ $item->Title }}
                                                @if ($msgsCount[$i] > 0)
                                                    <strong style="font-size: 18px">({{ $msgsCount[$i] }})</strong>
                                                @endif
                                            </a></td>
                                        @php($i++)
                                        <td>
                                            {{--<span class="badge bgc-red-50 c-red-700 p-10 lh-0 tt-c badge-pill">{{ substr($item->TimeLeft,1,strlen($item->TimeLeft)-1) }}</span>--}}
                                            <span class="badge bgc-green-50 c-green-700 p-10 lh-0 tt-c badge-pill">
                                                {{ substr($item->TimeLeft,1,stripos($item->TimeLeft,'M')) }}
                                            </span>
                                        </td>
                                        <td>{{ $item->ListingDetails->StartTime->format('d M Y') }}</td>
                                        <td>
                                            <span
                                                class="text-success">{{ $item->SellingStatus->CurrentPrice->value }} {{ $item->SellingStatus->CurrentPrice->currencyID }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')

@endsection
