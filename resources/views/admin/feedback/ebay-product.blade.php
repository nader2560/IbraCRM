@extends('admin.default')

@section('css')
    <script src="https://code.jquery.com/jquery-2.2.0.min.js" type="text/javascript"></script>
@endsection

@section('page-header')

@endsection

@section('content')
    <div align="center">
        <img class="imgc" src="{{asset('images/ebay_logo.png')}}" alt="ebay's logo" width="200px">
        <br><br>
        <a href="{{ route('feed') }}"><b>Back to the Hub</b></a> |
        <a href="./{{ $id }}"><b>Refresh Content</b></a> |
        <a href="{{ route('ebay') }}"><b>Sales</b></a>
    </div>
    <br><br>
    @if($response->PaginationResult->TotalNumberOfEntries === 0)
        <br><br>
        <div align="center" style="font-family: 'Raleway', sans-serif; font-size: 25px;"><b>Messages</b></div>
        <br>
        <div align="center" style="font-family: 'Raleway', sans-serif; font-size: 20px;">No messages or questions
            for this item yet!
        </div>
    @else
        @foreach($response->MemberMessage->MemberMessageExchange as $discussion)
            <div align="center">
                <div class="masonry-item">
                    <!-- #Chat ==================== -->
                    <div class="bd bgc-white">
                        <div class="layers">
                            <div class="layer w-100 p-20">
                                <h6 class="lh-1">{{ $discussion->Question->SenderID }}
                                    on {{ $discussion->CreationDate->format('d M Y') }}</h6>
                            </div>
                            <div class="layer w-100">
                                <!-- Chat Box -->
                                <div class="bgc-grey-200 p-20 gapY-15">
                                    <!-- Chat Conversation -->
                                    <div class="peers fxw-nw">
                                        <div class="peer peer-greed">
                                            <div class="layers ai-fs gapY-5">
                                                <div class="layer">
                                                    <div class="peers fxw-nw ai-c pY-3 pX-10 bgc-white bdrs-2 lh-3/2">
                                                        <div class="peer mR-10">
                                                            <small>{{ $discussion->CreationDate->format('H:i') }}</small>
                                                        </div>
                                                        <div class="peer-greed">
                                                            <span>{{ $discussion->Question->Body }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Chat Conversation -->
                                    <div class="peers fxw-nw ai-fe">
                                        <div class="peer peer-greed ord-0">
                                            <div class="layers ai-fe gapY-10">
                                                <div class="layer">
                                                    <div class="peers fxw-nw ai-c pY-3 pX-10 bgc-white bdrs-2 lh-3/2">
                                                        <div class="peer mL-10 ord-1">
                                                            <small id="msgTime{{ $discussion->Question->MessageID }}"></small>
                                                        </div>
                                                        <div class="peer-greed ord-0">
                                                            <span id="msgBody{{ $discussion->Question->MessageID }}"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Chat Send -->
                                <div class="p-20 bdT bgc-white">
                                    <div class="pos-r">
                                        @if($discussion->MessageStatus <> "Answered")
                                            <input type="text" class="form-control bdrs-10em m-0"
                                                   id="msgTxt{{ $discussion->Question->MessageID }}"
                                                   placeholder="Say something..." id="msgInput">
                                            <button type="button" id="msgBtn{{ $discussion->Question->MessageID }}"
                                                    class="btn btn-primary bdrs-50p w-2r p-0 h-2r pos-a r-1 t-1">
                                                <i class="fa fa-paper-plane-o"></i>
                                            </button>
                                        @else
                                            <input type="text" class="form-control bdrs-10em m-0"
                                                   id="msgTxt{{ $discussion->Question->MessageID }}"
                                                   placeholder="Already answered" id="msgInput" disabled="true">
                                            <button type="button" disabled="true"
                                                    id="msgBtn{{ $discussion->Question->MessageID }}"
                                                    class="btn btn-primary bdrs-50p w-2r p-0 h-2r pos-a r-1 t-1">
                                                <i class="fa fa-paper-plane-o"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <br>
        @endforeach
    @endif
@endsection

@section('js')
    @if($response->PaginationResult->TotalNumberOfEntries <> 0)
        @foreach($response->MemberMessage->MemberMessageExchange as $discussion)
            @if($discussion->MessageStatus <> "Answered")
                <script>
                    document.getElementById("msgBtn{{ $discussion->Question->MessageID }}").onclick = function () {
                        if (document.getElementById("msgTxt{{ $discussion->Question->MessageID }}").value != "") {
                            var now = new Date();
                            document.getElementById("msgTime{{ $discussion->Question->MessageID }}").innerText = now.getHours() + ':' + now.getMinutes();
                            document.getElementById("msgBody{{ $discussion->Question->MessageID }}").innerText = document.getElementById("msgTxt{{ $discussion->Question->MessageID }}").value;
                            document.getElementById("msgTxt{{ $discussion->Question->MessageID }}").disabled = true;
                            document.getElementById("msgBtn{{ $discussion->Question->MessageID }}").disabled = true;
                            $.ajax({
                                type: "POST",
                                url: '{{ route('respondebay') }}',
                                data: {
                                    body: document.getElementById("msgTxt{{ $discussion->Question->MessageID }}").value,
                                    msgId: "{{ $discussion->Question->MessageID }}",
                                    recId: "{{ $discussion->Question->SenderID }}"
                                }
                            }).done(function (msg) {
                                alert(msg);
                            }).fail(function(){
                                alert("Nothing done!");
                            });
                        }
                    }
                </script>
            @endif
        @endforeach
    @endif
@endsection
