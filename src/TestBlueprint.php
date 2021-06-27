<?php

namespace Jonassiewertsen\Jobs;

class TestBlueprint
{
    public function __invoke(): array
    {
        return [
            'title'    => 'Failed Job',
            'sections' => [
                'main' => [
                    'display' => 'Main',
                    'fields'  => [
                        [
                            'handle' => 'title',
                            'field'  => [
                                'type'     => 'text',
                                'required' => true,
                            ],
                        ],
                    ],
                ]],
            ];
        //        handle: uuid
        //        field:
        //          input_type: text
        //          antlers: false
        //          display: Job Uuid
        //          type: text
        //          icon: text
        //      -
        //        handle: slug
        //        field:
        //          type: slug
        //          required: true
        //          localizable: true
        //          generate: false
        //          display: Slug
        //          icon: slug
        //          listable: hidden
        //      -
        //        handle: connection
        //        field:
        //          input_type: text
        //          antlers: false
        //          display: Connection
        //          type: text
        //          icon: text
        //          listable: hidden
        //      -
        //        handle: queue
        //        field:
        //          input_type: text
        //          antlers: false
        //          display: Queue
        //          type: text
        //          icon: text
        //          listable: hidden
        //      -
        //        handle: payload
        //        field:
        //          antlers: false
        //          display: Payload
        //          type: textarea
        //          icon: textarea
        //          listable: hidden
        //      -
        //        handle: exception
        //        field:
        //          antlers: false
        //          display: Exception
        //          type: textarea
        //          icon: textarea
        //          listable: hidden
        //  sidebar:
        //    display: Sidebar
        //    fields:
        //      -
        //        handle: failed_at
        //        field:
        //          mode: single
        //          time_enabled: false
        //          time_required: false
        //          full_width: false
        //          inline: false
        //          columns: 1
        //          rows: 1
        //          display: 'Failed at'
        //          read_only: true
        //          type: date
        //          icon: date
    }
}
