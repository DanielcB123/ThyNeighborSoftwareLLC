<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnboardingSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('lead_sources')->updateOrInsert(
            ['slug' => 'website-start-project'],
            [
                'public_id' => (string) Str::ulid(),
                'name' => 'Website start-project form',
                'status' => 'active',
                'description' => 'Primary public inquiry intake through /start-project.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $template = DB::table('discovery_templates')
            ->where('slug', 'default-client-discovery')
            ->first();

        $templateId = $template?->id;

        if ($templateId === null) {
            $templateId = DB::table('discovery_templates')->insertGetId([
                'public_id' => (string) Str::ulid(),
                'name' => 'Default Client Discovery',
                'slug' => 'default-client-discovery',
                'status' => 'published',
                'description' => 'Baseline phased discovery template for start-project onboarding.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $version = DB::table('discovery_template_versions')
            ->where('discovery_template_id', $templateId)
            ->where('version_number', 1)
            ->first();

        $versionId = $version?->id;

        if ($versionId === null) {
            $versionId = DB::table('discovery_template_versions')->insertGetId([
                'public_id' => (string) Str::ulid(),
                'discovery_template_id' => $templateId,
                'version_number' => 1,
                'status' => 'published',
                'created_by' => 'system',
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $sections = [
            ['key' => 'project', 'title' => 'Project Direction', 'sort_order' => 10],
            ['key' => 'business', 'title' => 'Business Context', 'sort_order' => 20],
            ['key' => 'goals', 'title' => 'Goals and Constraints', 'sort_order' => 30],
            ['key' => 'preparation', 'title' => 'Preparation Materials', 'sort_order' => 40],
            ['key' => 'meeting', 'title' => 'Meeting Scheduling', 'sort_order' => 50],
        ];

        foreach ($sections as $section) {
            DB::table('discovery_section_definitions')->updateOrInsert(
                [
                    'discovery_template_version_id' => $versionId,
                    'section_key' => $section['key'],
                ],
                [
                    'public_id' => (string) Str::ulid(),
                    'title' => $section['title'],
                    'sort_order' => $section['sort_order'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $questions = [
            ['key' => 'project', 'section' => 'project', 'prompt' => 'What project direction should we plan for?'],
            ['key' => 'business', 'section' => 'business', 'prompt' => 'Who is leading the engagement and what context matters?'],
            ['key' => 'goals', 'section' => 'goals', 'prompt' => 'What outcomes and constraints define success?'],
            ['key' => 'preparation', 'section' => 'preparation', 'prompt' => 'What links and artifacts should we review ahead of discovery?'],
            ['key' => 'meeting', 'section' => 'meeting', 'prompt' => 'What meeting window and attendees should we schedule?'],
        ];

        foreach ($questions as $index => $question) {
            DB::table('discovery_question_definitions')->updateOrInsert(
                [
                    'discovery_template_version_id' => $versionId,
                    'question_key' => $question['key'],
                ],
                [
                    'public_id' => (string) Str::ulid(),
                    'section_key' => $question['section'],
                    'response_type' => 'object',
                    'prompt' => $question['prompt'],
                    'is_required' => true,
                    'sort_order' => ($index + 1) * 10,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
