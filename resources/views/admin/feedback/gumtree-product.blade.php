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
        <img class="imgc" src="{{asset('images/gumtree_logo.png')}}" alt="gumtree's logo" width="300px">
        <br><br>
        <a href="{{ route('feed') }}"><b>Back to the Hub</b></a> |
        <a href="./{{ $infos->adId }}"><b>Refresh Content</b></a> |
        <a href="{{ route('gumtree') }}"><b>Sales</b></a>
        <br><br>
        <h3 align="center" style="font-family: 'Raleway', sans-serif; font-size: 12px;"><u></u></h3>
        @if ($infos->rootImgUrl <> null)
            <img src="{{ $infos->rootImgUrl }}1.JPG" alt="product's picture" width="250px">
        @endif
        <br>
    </div>
    <br>
    <div class="tab">
        <button class="tablinks" disabled="true"><u>Product Review: {{ $infos->title }}</u></button>
        <button class="tablinks" onclick="openCity(event, 'London')" id="defaultOpen">
            Messages & Questions
            @if($msgsAll->numUnreadConversations <> 0)
                ({{ $msgsAll->numUnreadConversations }})
            @endif
        </button>
        <button class="tablinks" onclick="openCity(event, 'Paris')">Feedback & Comments</button>
        <button class="tablinks" onclick="openCity(event, 'Tunis')">Orders & Transactions</button>
    </div>
    <div id="London" class="tabcontent">
        <!-- Messages Content -->
        @if($msgsAll->numConversations == 0 || $sellingBoolean === false)
            <br><br>
            <div align="center" style="font-family: 'Raleway', sans-serif; font-size: 25px;"><b>Messages</b></div>
            <br>
            <div align="center" style="font-family: 'Raleway', sans-serif; font-size: 20px;">No messages or questions
                for this item yet!
            </div>
            <br><br>
        @else
            <div>
                <div class="masonry-item">
                    <div class="mT-30">
                        <div class="list-group">
                            @foreach($msgs as $msg)
                                <div class="list-group-item list-group-item-action"
                                     onclick="openThread('{{ $msg->id }}')">
                                    Conversation with <strong>{{ $msg->converseeName }}</strong>:
                                    "{{ $msg->lastMessage->body }}"
                                    <small>&nbsp;&nbsp;&nbsp; {{ str_before($msg->lastMessage->time,'.') }}</small>
                                </div>
                                <div id="div{{ $msg->id }}"></div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
    </div>
    <br>
    @endif
    <!-- EndContent -->
    <div id="Paris" class="tabcontent">
        <!-- Feedback Content -->
        <br><br>
        <div align="center" style="font-family: 'Raleway', sans-serif; font-size: 25px;"><b>Feedback</b></div>
        <br>
        <div align="center" style="font-family: 'Raleway', sans-serif; font-size: 20px;">Feedback aren't
            supported on this platform!
        </div>
        <br><br>
        <!-- EndContent -->
    </div>
    <div id="Tunis" class="tabcontent">
        <br><br>
        <div align="center" style="font-family: 'Raleway', sans-serif; font-size: 25px;"><b>Orders &
                Transactions</b></div>
        <br>
        <div align="center" style="font-family: 'Raleway', sans-serif; font-size: 20px;">Orders aren't supported on this
            platform!
        </div>
        <br><br>
    </div>
@endsection

@section('js')
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
    <script>
        function openThread(id) {
            if (document.getElementById('div' + id).innerHTML === "") {
                thrdView(id);
                document.getElementById('div' + id).innerHTML = document.getElementById('div' + id).innerHTML + '<button onclick="closeThread(\'' + id + '\')">Close</button>';
            }
        }

        function closeThread(id) {
            document.getElementById('div' + id).innerHTML = '';
        }

        function thrdView(id) {
            $.ajaxSetup({
                headers: {
                    'X-XSRF-TOKEN': decodeURIComponent(/XSRF-Token=([^;]*)/ig.exec(document.cookie)[1])
                }
            });

            $.ajax({
                type: "GET",
                url: '../msg/' + id,
                success: function (response) {
                    document.getElementById('div' + id).innerHTML = response + document.getElementById('div' + id).innerHTML;
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log("ERROR:");
                    console.log(JSON.stringify(jqXHR));
                    console.log("AJAX error: " + textStatus + ' : ' + errorThrown);
                }
            });
        }
    </script>
    <script>
        function clickedbtn(id,infos) {
            if (document.getElementById('input' + id).value != "") {
                console.log("hey");
                var now = new Date();
                document.getElementById('dd' + id).innerHTML += "<div class=\"peers fxw-nw ai-fe\">\n" +
                    "                    <div class=\"peer peer-greed ord-0\">\n" +
                    "                        <div class=\"layers ai-fe gapY-10\">\n" +
                    "                            <div class=\"layer\">\n" +
                    "                                <div class=\"peers fxw-nw ai-c pY-3 pX-10 bgc-white bdrs-2 lh-3/2\">\n" +
                    "                                    <div class=\"peer mL-10 ord-1\">\n" +
                    "                                        <small>now</small>\n" +
                    "                                    </div>\n" +
                    "                                    <div class=\"peer-greed ord-0\"><span>" + document.getElementById('input' + id).value + "</span></div>\n" +
                    "                                </div>\n" +
                    "                            </div>\n" +
                    "                        </div>\n" +
                    "                    </div>\n" +
                    "                </div>";

                $.ajaxSetup({
                    headers: {
                        'X-XSRF-TOKEN': decodeURIComponent(/XSRF-Token=([^;]*)/ig.exec(document.cookie)[1])
                    }
                });

                $.ajax({
                    type: "POST",
                    url: '../msgsend',
                    data: {
                        'id': id,
                        'message': document.getElementById('input' + id).value,
                        'ad_id': infos
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
@endsection
