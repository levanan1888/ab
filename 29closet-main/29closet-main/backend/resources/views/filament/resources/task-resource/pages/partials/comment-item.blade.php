@php
    $marginLeft = min(($level ?? 0) * 18, 72);
    $children = $comment->replies ?? collect();
    $expanded = $this->isRepliesExpanded($comment->id);
    $visibleChildren = $expanded ? $children : $children->take(3);
@endphp

<article class="rm-history-item" style="margin-left: {{ $marginLeft }}px;" wire:key="comment-{{ $comment->id }}">
    <div><strong>{{ $comment->user?->name ?? 'N/A' }}</strong></div>
    <div>{{ $comment->content }}</div>
    <div class="rm-meta">{{ $comment->created_at?->diffForHumans() }}</div>
    <button type="button" class="rm-action-link" wire:click="setReplyTo({{ $comment->id }})">Trả lời</button>

    @if ($reply_to_comment_id === $comment->id)
        <div class="rm-reply-form">
            <div class="rm-reply-form-title">Trả lời bình luận</div>
            <textarea class="rm-reply-box" wire:model.defer="reply_content" placeholder="Viết phản hồi..."></textarea>
            <div style="margin-top: 8px; display: flex; gap: 8px; align-items: center;">
                <button type="button" class="rm-btn" wire:click.prevent="submitReply">Gửi trả lời</button>
                <button type="button" class="rm-action-link" wire:click="$set('reply_to_comment_id', null); $set('reply_content', '')">Hủy</button>
            </div>
        </div>
    @endif

    @foreach ($visibleChildren as $reply)
        @include('filament.resources.task-resource.pages.partials.comment-item', [
            'comment' => $reply,
            'level' => ($level ?? 0) + 1,
            'reply_to_comment_id' => $reply_to_comment_id,
        ])
    @endforeach

    @if (! $expanded && $children->count() > 3)
        <button type="button" class="rm-action-link" wire:click.prevent="loadMoreReplies({{ $comment->id }})">
            Hiển thị thêm ({{ $children->count() - 3 }})
        </button>
    @endif
</article>
