@layout('main')

@section('content')
	Welcome, {{ $user->info()['name'] }}!<br>
	<img src="{{ $user->picture() }}"><br><br>

	@foreach( $user->actions()->get() as $action )
		<strong>{{ Action::subclass($action->type) }}</strong>:
		@if ( $action->check() )
			done
		@else
			not done
		@endif
		<br>
	@endforeach

	@foreach( $user->friends() as $friend )
		{{ $friend->name }}
		<img src="{{ $friend->picture() }}"><br>
	@endforeach
@endsection
