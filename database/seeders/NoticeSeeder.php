<?php

namespace Database\Seeders;

use App\Models\Notice;
use Illuminate\Database\Seeder;

class NoticeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'type'       => 'register',
                'name'       => __('seeds.notices.register_name'),
                'text'       => __('seeds.notices.register_text'),
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'       => 'down_upload',
                'name'       => __('seeds.notices.down_upload_name'),
                'text'       => __('seeds.notices.down_upload_text'),
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'       => 'down_publish',
                'name'       => __('seeds.notices.down_publish_name'),
                'text'       => __('seeds.notices.down_publish_text'),
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'       => 'down_unpublish',
                'name'       => __('seeds.notices.down_unpublish_name'),
                'text'       => __('seeds.notices.down_unpublish_text'),
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'       => 'down_change',
                'name'       => __('seeds.notices.down_change_name'),
                'text'       => __('seeds.notices.down_change_text'),
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'       => 'notify',
                'name'       => __('seeds.notices.notify_name'),
                'text'       => __('seeds.notices.notify_text'),
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'       => 'invite',
                'name'       => __('seeds.notices.invite_name'),
                'text'       => __('seeds.notices.invite_text'),
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'       => 'contact',
                'name'       => __('seeds.notices.contact_name'),
                'text'       => __('seeds.notices.contact_text'),
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'       => 'ignore',
                'name'       => __('seeds.notices.ignore_name'),
                'text'       => __('seeds.notices.ignore_text'),
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'       => 'transfer',
                'name'       => __('seeds.notices.transfer_name'),
                'text'       => __('seeds.notices.transfer_text'),
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'       => 'rating',
                'name'       => __('seeds.notices.rating_name'),
                'text'       => __('seeds.notices.rating_text'),
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'       => 'surprise',
                'name'       => __('seeds.notices.surprise_name'),
                'text'       => __('seeds.notices.surprise_text'),
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'       => 'explain',
                'name'       => __('seeds.notices.explain_name'),
                'text'       => __('seeds.notices.explain_text'),
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
        ];

        Notice::query()->truncate();
        Notice::query()->insert($data);
    }
}
