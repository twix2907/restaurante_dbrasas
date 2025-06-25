<ul class="mt-2">
    @foreach ($errors->all() as $error)
    <li style="color: red">
        {{$error}}
    </li>
        
    @endforeach
</ul>