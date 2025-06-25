<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions\Notification;

use Flarum\Discussion\Discussion;
use Flarum\Notification\Blueprint\BlueprintInterface;
use Flarum\Notification\MailableInterface;
use Flarum\User\User;
use Illuminate\Support\Arr;
use Symfony\Contracts\Translation\TranslatorInterface;

class DiscussionMergedBlueprint implements BlueprintInterface, MailableInterface
{
    /**
     * @var Discussion
     */
    public $discussion;

    /**
     * @var User
     */
    public $actor;

    /**
     * @var array
     */
    public $mergedDiscussion;

    public function __construct(Discussion $discussion, User $actor, array $mergedDiscussion)
    {
        $this->discussion = $discussion;
        $this->actor = $actor;
        $this->mergedDiscussion = $mergedDiscussion;
    }

    /**
     * Get the user that sent the notification.
     */
    public function getFromUser()
    {
        return $this->actor;
    }

    /**
     * Get the model that is the subject of this activity.
     */
    public function getSubject()
    {
        return $this->discussion;
    }

    /**
     * Get the data to be stored in the notification.
     */
    public function getData()
    {
        return [
            'merged_title' => Arr::get($this->mergedDiscussion, 'title'),
            'merged_id'    => Arr::get($this->mergedDiscussion, 'id'),
        ];
    }

    /**
     * Get the serialized type of this activity.
     *
     * @return string
     */
    public static function getType()
    {
        return 'discussionMerged';
    }

    /**
     * Get the name of the model class for the subject of this activity.
     *
     * @return string
     */
    public static function getSubjectModel()
    {
        return Discussion::class;
    }

    /**
     * Get the name of the view to construct a notification email with.
     *
     * @return array
     */
    public function getEmailView()
    {
        return ['text' => 'fof-merge-discussions::emails.discussionMerged'];
    }

    /**
     * Get the subject line for the notification email.
     *
     * @return string
     */
    public function getEmailSubject(TranslatorInterface $translator)
    {
        return $translator->trans('fof-merge-discussions.email.merged.subject', [
            '{display_name}'            => $this->actor->display_name,
            '{discussion_title}'        => $this->discussion->title,
            '{merged_discussion_title}' => Arr::get($this->mergedDiscussion, 'title'),
        ]);
    }
}
