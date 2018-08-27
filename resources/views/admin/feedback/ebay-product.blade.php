@extends('admin.default')

@section('css')
    <meta name="csrf_token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-2.2.0.min.js" type="text/javascript"></script>
    <style>
        /* Style the tab */
        .tab {
            overflow: hidden;
            border: 1px solid #ccc;
            background-color: #f1f1f1;
        }

        /* Style the buttons that are used to open the tab content */
        .tab button {
            background-color: inherit;
            float: left;
            border: none;
            outline: none;
            cursor: pointer;
            padding: 14px 16px;
            transition: 0.3s;
        }

        /* Change background color of buttons on hover */
        .tab button:hover {
            background-color: #ddd;
        }

        /* Create an active/current tablink class */
        .tab button.active {
            background-color: #ccc;
        }

        /* Style the tab content */
        .tabcontent {
            display: none;
            padding: 6px 12px;
            border: 1px solid #ccc;
            border-top: none;
        }

        .tabcontent {
            animation: fadeEffect 1s; /* Fading effect takes 1 second */
        }

        /* Go from zero to full opacity */
        @keyframes fadeEffect {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
    </style>
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
        <br><br>
        <h3 align="center" style="font-family: 'Raleway', sans-serif; font-size: 12px;"><u></u></h3>
    </div>
    <br>
    <div class="tab">
        <button class="tablinks" disabled="true"><u>Product Review: {{ $itemName }}</u></button>
        <button class="tablinks" onclick="openCity(event, 'London')" id="defaultOpen">
            Messages & Questions
            @if($msgCount <> 0)
                ({{ $msgCount }})
            @endif
        </button>
        <button class="tablinks" onclick="openCity(event, 'Paris')">Feedback & Comments</button>
    </div>
    <div id="London" class="tabcontent">
        <!-- Messages Content -->
        @if($response->PaginationResult->TotalNumberOfEntries === 0)
            <br><br>
            <div align="center" style="font-family: 'Raleway', sans-serif; font-size: 25px;"><b>Messages</b></div>
            <br>
            <div align="center" style="font-family: 'Raleway', sans-serif; font-size: 20px;">No messages or questions
                for this item yet!
            </div>
            <br><br>
        @else
            <div align="center">
                <div class="masonry-item">
                @foreach($response->MemberMessage->MemberMessageExchange as $discussion)
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
                                    @if($discussion->MessageStatus <> "Answered")
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
                                        @endif
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
                        <br>
                    @endforeach
                </div>
            </div>
            <br>
    @endif
    <!-- EndContent -->
    </div>
    <div id="Paris" class="tabcontent">
        <!-- Feedback Content -->
        @if ($response2->PaginationResult->TotalNumberOfEntries === 0)
            <br><br>
            <div align="center" style="font-family: 'Raleway', sans-serif; font-size: 25px;"><b>Feedback</b></div>
            <br>
            <div align="center" style="font-family: 'Raleway', sans-serif; font-size: 20px;">No feedback or comments
                for this item yet!
            </div>
            <br><br>
        @else
            <br><br>
            <div class="masonry-item">
                <div class="bd bgc-white p-20">
                    <div class="layers">
                        <div class="layer w-100 mB-10">
                            <h6 class="lh-1" align="center">Feedback & Comments</h6>
                        </div>
                        <div class="layer w-100">
                            <ul class="list-task list-group" data-role="tasklist">
                                @foreach ($response2->FeedbackDetailArray->FeedbackDetail as $feedback)
                                    @if($feedback->Role === "Seller")
                                        <li class="list-group-item bdw-0" data-role="task">
                                            <div class="peers ai-c">
                                                <label class=" peers peer-greed js-sb ai-c">
                                                    <span class="peer peer-greed">User: {{ $feedback->CommentingUser }}</span>
                                                    <span class="peer peer-greed">{{ $feedback->CommentText }}</span>
                                                    <span class="peer">
                                                {{ $feedback->CommentTime->format('d M Y') }}
                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        @if($feedback->CommentType === "Positive")
                                                            <span class="badge badge-pill fl-r badge-success lh-0 p-10">Positive</span>
                                                        @elseif($feedback->CommentType === "Negative")
                                                            <span class="badge badge-pill fl-r badge-danger lh-0 p-10">Negative</span>
                                                        @elseif($feedback->CommentType === "Neutral")
                                                            <span class="badge badge-pill fl-r badge-info lh-0 p-10">Neutral</span>
                                                        @else
                                                            <span class="badge badge-pill fl-r badge-light lh-0 p-10">Withdrawn</span>
                                                        @endif
                                                </span>
                                                </label>
                                                &nbsp;&nbsp;
                                                @if($feedback->FeedbackResponse === null)
                                                    <button type="button" id="fbBtn{{ $feedback->FeedbackID }}"
                                                            class="btn btn-primary bdrs-50p w-2r p-0 h-2r  r-1 t-1">
                                                        <i class="fa fa-paper-plane-o"></i>
                                                    </button>
                                                    <br>
                                                    <input type="text" class="form-control bdrs-10em m-0"
                                                           id="fbTxt{{ $feedback->FeedbackID }}"
                                                           placeholder="Say something...">
                                                @else
                                                    <button disabled="true" type="button" id="fbBtnInactive"
                                                            class="btn btn-primary bdrs-50p w-2r p-0 h-2r  r-1 t-1">
                                                        <i class="fa fa-paper-plane-o"></i>
                                                    </button>
                                                    <br>
                                                    <input disabled="true" type="text"
                                                           class="form-control bdrs-10em m-0"
                                                           id="fbTxtInactive"
                                                           placeholder="{{ $feedback->FeedbackResponse }}">
                                                @endif
                                            </div>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <br><br>
    @endif
    <!-- EndContent -->
    </div>
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
                            $.ajaxSetup({
                                headers: {
                                    'X-XSRF-TOKEN': decodeURIComponent(/XSRF-Token=([^;]*)/ig.exec(document.cookie)[1])
                                }
                            });

                            $.ajax({
                                type: "POST",
                                url: '../msg',
                                data: {
                                    'body': document.getElementById("msgTxt{{ $discussion->Question->MessageID }}").value,
                                    'msgId': "{{ $discussion->Question->MessageID }}",
                                    'recId': "{{ $discussion->Question->SenderID }}"
                                },
                                dataType: 'json',
                                success: function (response) {
                                    console.log("Response:");
                                    console.log(response);
                                },
                                error: function (jqXHR, textStatus, errorThrown) {
                                    console.log("ERROR:");
                                    console.log(JSON.stringify(jqXHR));
                                    console.log("AJAX error: " + textStatus + ' : ' + errorThrown);
                                }
                            });
                        }
                    }
                </script>
            @endif
        @endforeach
    @endif
    <script>
        document.getElementById("defaultOpen").click();

        function openCity(evt, cityName) {
            // Declare all variables
            var i, tabcontent, tablinks;

            // Get all elements with class="tabcontent" and hide them
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }

            // Get all elements with class="tablinks" and remove the class "active"
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }

            // Show the current tab, and add an "active" class to the button that opened the tab
            document.getElementById(cityName).style.display = "block";
            evt.currentTarget.className += " active";
        }
    </script>
    @if ($response2->PaginationResult->TotalNumberOfEntries <> 0)
        @foreach ($response2->FeedbackDetailArray->FeedbackDetail as $feedback)
            @if($feedback->Role === "Seller" && $feedback->FeedbackResponse === null)
                <script>
                    document.getElementById("fbBtn{{ $feedback->FeedbackID }}").onclick = function () {
                        if (document.getElementById("fbTxt{{ $feedback->FeedbackID }}").value != "") {
                            document.getElementById("fbTxt{{ $feedback->FeedbackID }}").disabled = true;
                            document.getElementById("fbBtn{{ $feedback->FeedbackID }}").disabled = true;

                            $.ajaxSetup({
                                headers: {
                                    'X-XSRF-TOKEN': decodeURIComponent(/XSRF-Token=([^;]*)/ig.exec(document.cookie)[1])
                                }
                            });

                            $.ajax({
                                type: "POST",
                                url: '../fbmsg',
                                data: {
                                    'body': document.getElementById("fbTxt{{ $feedback->FeedbackID }}").value,
                                    'msgId': "{{ $feedback->FeedbackID }}",
                                    'recId': "{{ $feedback->CommentingUser }}"
                                },
                                dataType: 'json',
                                success: function (response) {
                                    console.log("Response:");
                                    console.log(response);
                                },
                                error: function (jqXHR, textStatus, errorThrown) {
                                    console.log("ERROR:");
                                    console.log(JSON.stringify(jqXHR));
                                    console.log("AJAX error: " + textStatus + ' : ' + errorThrown);
                                }
                            });
                        }
                    }
                </script>
            @endif
        @endforeach
    @endif
@endsection