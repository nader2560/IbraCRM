<div class="layer w-100">
    <div id="dd{{ $id }}" class="bgc-grey-200 p-20 gapY-15">
        @foreach($msgs as $msg)
            @if ($msg->direction === "INBOUND")
                <div class="peers fxw-nw">
                    <div class="peer peer-greed">
                        <div class="layers ai-fs gapY-5">
                            <div class="layer">
                                <div class="peers fxw-nw ai-c pY-3 pX-10 bgc-white bdrs-2 lh-3/2">
                                    <div class="peer mR-10">
                                        <small>{{ str_before($msg->time,'.') }}</small>
                                    </div>
                                    <div class="peer-greed"><span>{{ $msg->body }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="peers fxw-nw ai-fe">
                    <div class="peer peer-greed ord-0">
                        <div class="layers ai-fe gapY-10">
                            <div class="layer">
                                <div class="peers fxw-nw ai-c pY-3 pX-10 bgc-white bdrs-2 lh-3/2">
                                    <div class="peer mL-10 ord-1">
                                        <small>{{ str_before($msg->time,'.') }}</small>
                                    </div>
                                    <div class="peer-greed ord-0"><span>{{ $msg->body }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
    <div class="p-20 bdT bgc-white">
        <div class="pos-r"><input id="input{{ $id }}" type="text" class="form-control bdrs-10em m-0"
                                  placeholder="Say something...">
            <button id="btn{{ $id }}" type="button" class="btn btn-primary bdrs-50p w-2r p-0 h-2r pos-a r-1 t-1"><i
                        class="fa fa-paper-plane-o" onclick="clickedbtn('{{ $id }}','{{ $prodId }}')"></i></button>
        </div>
    </div>
</div>