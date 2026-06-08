<div style="margin-top: 14px;">
    @forelse (($this->show_all_comments ? $comments : $comments->take(3)) as $comment)
        @include('filament.resources.task-resource.pages.partials.comment-item', [
            'comment' => $comment,
            'level' => 0,
            'reply_to_comment_id' => $reply_to_comment_id,
        ])
    @empty
        <div class="rm-meta">Chưa có ghi chú.</div>
    @endforelse

    @if (! $this->show_all_comments && $comments->count() > 3)
        <div style="margin-top: 8px;">
            <button type="button" class="rm-action-link" wire:click.prevent="loadMoreComments">
                Hiển thị thêm ({{ $comments->count() - 3 }})
            </button>
        </div>
    @endif
</div>
