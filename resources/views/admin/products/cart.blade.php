@extends('admin.default')

@section('css')
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
@endsection

@section('page-header')

@endsection

@section('content')

    <div class="table-responsive p-20">
        <table class="table">
            <thead>
            <tr>
                <th class=" bdwT-0"></th>
                <th class=" bdwT-0">Product Image</th>
                <th class=" bdwT-0">Product Title</th>
                <th class=" bdwT-0">Number Of Items</th>

            </tr>
            </thead>
            <tbody>
            {{--@php($i = 0)--}}
            @foreach($response as $item)
                @if($item!=null)
                    <tr>
                        <td><div class="checkbox checkbox-circle checkbox-info peers ai-c">
                                <input type="checkbox" id="{{$item['ID']}}" name="checker" class="peer">
                                <label/>
                            </div></td>
                        <td class="fw-600"><img src="{{$item['guid']}}" width=300px/></td>
                        {{--@php($i++)--}}
                        <td>
                            {{--<span class="badge bgc-red-50 c-red-700 p-10 lh-0 tt-c badge-pill">{{ substr($item->TimeLeft,1,strlen($item->TimeLeft)-1) }}</span>--}}
                            {{ $item['post_title']}}
                        </td>
                        <td>
                            <input id="number" class="form-control" name="number" value="0">
                        </td>
                    </tr>
                @endif
            @endforeach
            </tbody>
        </table>
    </div>
    <form name="register_complaint" id="register_complaint_frm">
    <div>
        <div align="left">
        <input id="linkGen" class="form-control" name="Link" value="">
        </div>

        <div align="right">
            <button class="btn btn-primary" onclick="genrateLink(this.form.elements['Link'])">Generate</button>
        </div>
    </div>
    </form>

    <script language="Javascript" type="text/javascript">
        function genrateLink(txtElement) {
            event.preventDefault();
            var c = document.getElementsByName('checker');
            var num = document.getElementsByName('number');
            var j = c.length;
            var stringa = "http://icinghouse.co.uk/index.php/view-cart?fill_cart=";
            var checkedNumber = 0;
            for(var i=0; i<j ;i++)
            {
                if (c[i].checked){
                    checkedNumber++;
                    var temp = "";
                    if( num[i].value>0 ){
                        temp = temp.concat(parseInt(num[i].value.toString()).toString(),"x",c[i].id.toString(),",");
                        stringa = stringa.concat(temp);
                    }else {
                        stringa = stringa.concat(c[i].id.toString(),",");
                    }
                }
                if(i==j-1){
                    stringa = stringa.substring(0,stringa.length-1);
                }
            }
            if(checkedNumber>0)
            txtElement.value = stringa;
            return false;
        }
    </script>
@endsection

@section('js')

@endsection
