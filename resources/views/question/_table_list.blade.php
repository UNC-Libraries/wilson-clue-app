<table class="table table-hover" id="questionIndexTable">
    <thead>
    <tr>
        <th>Question</th>
        <th>Full Answer</th>
        <th>Incorrect Count</th>
    </tr>
    </thead>
    <tbody>
    @foreach($models as $question)
        <tr class='clickable-row' data-href='{{ route('admin.question.edit',array($question->id)) }}'>
            <td>
                @if($question->type == 1)
                    {{ html()->img(asset(asset($question->src)), 'question-image')->class('media-object-128') }}
                @else
                    {{ $question->text }}
                @endif
            </td>
            <td>{{ $question->full_answer }}</td>
            <td>{{ $question->incorrectAnswers->count() }}</td>
        </tr>
    @endforeach
    </tbody>
</table>