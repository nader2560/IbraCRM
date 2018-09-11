@extends('admin.default')

@section('css')

@endsection

@section('page-header')

@endsection

@section('content')
    <div align="center">
        <img class="imgc" src="{{asset('images/gumtree_logo.png')}}" alt="gumtree's logo" width="300px">
        <br><br>
        <a href="{{ route('feed') }}"><b>Back to the Hub</b></a> |
        <a href="{{ route('gumtree') }}"><b>Refresh Content</b></a> |
        <a href="{{ route('gumtree') }}"><b>Sales</b></a>
    </div>
    <br><br>
    @if(count($ads) === 0)
        <br>
        <div align="center" style="font-family: 'Raleway', sans-serif; font-size: 20px;">No products posted yet!
        </div>
        <br><br>
    @else
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
                                        <h5>Total Listing Views</h5>
                                        <p class="mB-0">Account State: Active</p>
                                    </div>
                                    <div class="peer">
                                        <h3 class="text-right">{{ $numV }} </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive p-20">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th class=" bdwT-0">Title</th>
                                        <th class=" bdwT-0">Start Date / Status</th>
                                        <th class=" bdwT-0">Display Price</th>
                                        <th class=" bdwT-0">Listing Views</th>
                                        <th class=" bdwT-0">Views</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php($i = 0)
                                    @foreach($ads as $ad)
                                        <tr>
                                            <td class="fw-600"><a
                                                        href="./gmt/product/{{ $ad->adId }}">{{ $ad->title }}
                                                    @if ($urdmsgs[$i] > 0)
                                                        <strong style="font-size: 18px">({{ $urdmsgs[$i] }})</strong>
                                                    @endif
                                                </a></td>
                                            <td>
                                            <span class="badge bgc-green-50 c-green-700 p-10 lh-0 tt-c badge-pill">
                                                @if(isset($ad->postedTime))
                                                    {{ $ad->postedTime }} / {{ $ad->status }}
                                                @else
                                                    {{ $ad->status }}
                                                @endif
                                            </span>
                                            </td>
                                            <td>
                                                @if ($ad->displayPrice <> "")
                                                    {{ $ad->displayPrice }}
                                                @else
                                                    £0.00
                                                @endif
                                            </td>
                                            <td>
                                            <span
                                                    class="text-success">{{ $ad->listingViews }}</span>
                                            </td>
                                            <td>
                                                <span class="text-success">{{ $ad->views }}</span>
                                            </td>
                                        </tr>
                                        @php($i++)
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('js')

@endsection
