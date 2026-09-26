<?php

namespace App\Helpers;

class AiStepResponseContractHelper
{
    public static function rules(string $stepName): array
    {
        return match ($stepName) {
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP1 => self::step1Rules(),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2 => self::step2Rules(),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP3 => self::step3Rules(),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP4 => self::step4Rules(),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP5 => self::step5Rules(),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP6 => self::step6Rules(),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP7 => self::step7Rules(),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP8 => self::step8Rules(),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP9 => self::step9Rules(),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP10 => self::step10Rules(),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP11 => self::step11Rules(),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP12 => self::step12Rules(),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP13 => self::step13Rules(),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP14 => self::step14Rules(),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP15 => self::step15Rules(),
            default => [],
        };
    }

    public static function integerFields(string $stepName): array
    {
        return match ($stepName) {
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP13 => ['chapter_plan.*.chapter_number'],
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP14 => [
                'page_plan.*.page_number',
                'page_plan.*.chapter_reference',
                'page_plan.*.estimated_word_count',
            ],
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP15 => ['chapter_number'],
            default => [],
        };
    }

    public static function payloadKey(string $stepName): ?string
    {
        return match ($stepName) {
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP1 => 'novel_foundation',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2 => 'characters',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP3 => 'world_bible',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP4 => 'locations',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP5 => 'factions',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP6 => 'creatures',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP7 => 'systems',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP8 => 'timeline',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP9 => 'story_structure',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP10 => 'twists_and_foreshadowing',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP11 => 'scene_plans',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP12 => 'dialogue_plans',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP13 => 'chapter_plan',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP14 => 'page_plan',
            default => null,
        };
    }

    public static function objects(string $stepName): array
    {
        return [
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2 => ['characters', 'relationships'],
        ][$stepName] ?? [];
    }

    private static function strings(array $keys): array
    {
        $rules = [];

        foreach ($keys as $key) {
            $rules[$key] = ['required', 'string'];
        }

        return $rules;
    }

    private static function lists(array $keys, bool $requireItems = false): array
    {
        $rules = [];

        foreach ($keys as $key) {
            $rules[$key] = $requireItems ? ['required', 'array', 'min:1'] : ['required', 'array'];
        }

        return $rules;
    }

    private static function integers(array $keys): array
    {
        $rules = [];

        foreach ($keys as $key) {
            $rules[$key] = ['required', 'integer'];
        }

        return $rules;
    }

    private static function objectLists(string $prefix, array $keys, array $itemLists = []): array
    {
        $rules = [$prefix => ['required', 'array', 'min:1']];

        foreach ($keys as $key) {
            $rules[$prefix.'.*.'.$key] = ['required', 'string'];
        }

        foreach ($itemLists as $key) {
            $rules[$prefix.'.*.'.$key] = ['required', 'array'];
        }

        return $rules;
    }

    private static function step1Rules(): array
    {
        return array_merge(
            self::strings(['novel_title', 'novel_subtitle']),
            ['novel_foundation' => ['required', 'array']],
            self::strings([
                'novel_foundation.premise', 'novel_foundation.story_concept', 'novel_foundation.narrative_hook',
                'novel_foundation.central_question', 'novel_foundation.central_theme',
                'novel_foundation.emotional_direction', 'novel_foundation.setting',
                'novel_foundation.main_character_direction', 'novel_foundation.central_motivation',
                'novel_foundation.central_goal', 'novel_foundation.character_journey_direction',
                'novel_foundation.central_conflict', 'novel_foundation.opposing_force',
                'novel_foundation.stakes', 'novel_foundation.consequences',
                'novel_foundation.opening_situation', 'novel_foundation.inciting_event',
                'novel_foundation.initial_goal', 'novel_foundation.escalation',
                'novel_foundation.climax_direction', 'novel_foundation.resolution_direction',
            ]),
            self::lists([
                'novel_foundation.important_character_roles', 'novel_foundation.major_complications',
                'novel_foundation.discoveries', 'novel_foundation.turning_points',
                'novel_foundation.themes',
            ], true),
        );
    }

    private static function step2Rules(): array
    {
        return array_merge(
            self::lists(['characters', 'relationships'], true),
            self::strings([
                'characters.*.name', 'characters.*.role', 'characters.*.character_type',
                'characters.*.personality', 'characters.*.appearance_direction', 'characters.*.background_direction',
                'characters.*.motivation', 'characters.*.goal', 'characters.*.internal_conflict',
                'characters.*.external_conflict', 'characters.*.relationship_to_main_character',
                'characters.*.character_arc_direction',
            ]),
            self::lists([
                'characters.*.strengths', 'characters.*.weaknesses',
            ]),
            self::strings([
                'relationships.*.characters', 'relationships.*.relationship', 'relationships.*.story_purpose',
            ]),
        );
    }

    private static function step3Rules(): array
    {
        return array_merge(
            ['world_bible' => ['required', 'array']],
            [
                'world_bible.world_overview' => ['required', 'array'],
                'world_bible.history_and_lore' => ['required', 'array'],
            ],
            self::strings([
                'world_bible.world_overview.world_name_and_nature',
                'world_bible.world_overview.world_scale',
                'world_bible.world_overview.geography_direction',
                'world_bible.world_overview.dominant_environment',
                'world_bible.world_overview.world_state_at_story_start',
                'world_bible.world_overview.connection_to_story_conflict',
                'world_bible.world_overview.tone_and_atmosphere',
                'world_bible.history_and_lore.how_history_shaped_current_tensions',
                'world_bible.history_and_lore.historical_connection_to_story_conflict',
            ]),
            self::lists([
                'world_bible.history_and_lore.significant_eras',
                'world_bible.history_and_lore.major_historical_events',
                'world_bible.history_and_lore.legends_and_myths',
                'world_bible.history_and_lore.origin_stories',
                'world_bible.history_and_lore.world_turning_points',
                'world_bible.history_and_lore.secrets_and_forgotten_knowledge',
            ], true),
            self::objectLists('world_bible.cultures_and_societies', [
                'name', 'social_structure', 'daily_life', 'class_system_and_power', 'relationship_to_story',
            ], ['customs_and_traditions', 'beliefs_and_values']),
            self::objectLists('world_bible.rules_and_systems', [
                'system_name', 'system_type', 'source_of_power', 'costs_and_limitations',
                'governing_laws_and_order', 'impact_on_story',
            ], []),
            self::objectLists('world_bible.key_world_elements', [
                'element_name', 'element_description', 'significance',
                'connection_to_characters', 'story_influence',
            ], []),
        );
    }

    private static function step4Rules(): array
    {
        return array_merge(
            ['locations' => ['required', 'array']],
            ['locations.environment_details' => ['required', 'array']],
            self::objectLists('locations.major_locations', [
                'name', 'type', 'geographic_position', 'description_and_atmosphere',
                'importance_to_world', 'connection_to_story_conflict',
            ], []),
            self::objectLists('locations.cities_and_regions', [
                'name', 'population_and_culture', 'layout_and_architecture', 'economy_and_governance',
                'social_atmosphere', 'connection_to_characters',
            ], []),
            self::objectLists('locations.important_places', [
                'name', 'type', 'location_in_world', 'purpose_and_function',
                'description_and_atmosphere', 'significance_to_story',
            ], []),
            self::strings([
                'locations.environment_details.climate_and_weather',
                'locations.environment_details.terrain_and_natural_features',
                'locations.environment_details.flora_and_fauna',
                'locations.environment_details.unique_characteristics',
                'locations.environment_details.effect_on_daily_life_and_travel',
                'locations.environment_details.connection_to_conflict',
            ]),
            self::objectLists('locations.location_significance', [
                'location_name', 'role_in_narrative', 'connection_to_character_goals',
                'connection_to_central_conflict', 'emotional_or_thematic_importance',
            ], ['likely_events']),
        );
    }

    private static function step5Rules(): array
    {
        return array_merge(
            ['factions' => ['required', 'array']],
            self::objectLists('factions.factions', [
                'name', 'type', 'purpose', 'structure_and_hierarchy', 'influence_and_reach',
                'connection_to_story_conflict',
            ], []),
            self::objectLists('factions.organizations', [
                'name', 'type', 'mission_and_function', 'membership_direction',
                'resources_and_power', 'connection_to_story',
            ], []),
            self::objectLists('factions.ideologies_and_goals', [
                'faction_name', 'core_ideology', 'methods_and_limitations', 'how_ideology_drives_actions',
            ], ['values_and_beliefs', 'ultimate_goals']),
            self::objectLists('factions.key_members', [
                'name', 'faction_name', 'position', 'role_and_responsibility',
                'motivation_and_personal_goals', 'relationship_to_main_characters', 'narrative_importance',
            ], []),
            self::objectLists('factions.relationships_and_conflicts', [
                'factions_involved', 'relationship_type', 'alliance_details', 'source_of_conflict',
                'historical_grievances', 'effect_on_story',
            ], []),
            self::objectLists('factions.influence_in_story', [
                'faction_name', 'role_in_central_conflict', 'effect_on_main_characters',
                'influence_on_key_events', 'progression_of_influence', 'contribution_to_themes',
            ], []),
        );
    }

    private static function step6Rules(): array
    {
        return array_merge(
            ['creatures' => ['required', 'array']],
            self::objectLists('creatures.creatures_and_species', [
                'name', 'species_or_being_type', 'classification', 'found_in', 'purpose_in_world',
            ], []),
            self::objectLists('creatures.traits_and_abilities', [
                'creature_name',
            ], [
                'physical_traits', 'natural_abilities', 'special_powers_or_features',
                'strengths', 'weaknesses', 'limitations',
            ]),
            self::objectLists('creatures.behavior_and_ecology', [
                'creature_name', 'behavioral_patterns', 'diet_and_survival', 'habitat_and_territory',
                'reproduction_or_propagation', 'social_structure', 'relationship_with_environment',
                'relationship_with_other_creatures',
            ], []),
            self::objectLists('creatures.role_in_world_and_story', [
                'creature_name', 'role_within_world', 'connection_to_cultures_and_factions',
                'connection_to_central_conflict', 'presence_in_key_events', 'contribution_to_themes',
                'narrative_importance',
            ], []),
            self::objectLists('creatures.visual_descriptions', [
                'creature_name', 'overall_appearance', 'size_and_silhouette', 'coloring_and_texture',
                'distinctive_markings', 'movement_and_mannerisms', 'sensory_presence',
            ], []),
        );
    }

    private static function step7Rules(): array
    {
        return array_merge(
            ['systems' => ['required', 'array']],
            self::objectLists('systems.system_types_and_rules', [
                'name', 'type', 'core_purpose', 'source_of_power_or_function', 'scope_of_system',
            ], ['fundamental_rules']),
            self::objectLists('systems.mechanics_and_limitations', [
                'system_name', 'how_it_is_used', 'conditions_and_requirements', 'costs_and_consequences',
                'restrictions_and_limits', 'balance_and_fairness', 'failure_conditions',
            ], []),
            self::objectLists('systems.effect_on_society_and_story', [
                'system_name', 'impact_on_daily_life', 'impact_on_culture_and_institutions',
                'impact_on_economy_and_politics', 'who_benefits_and_who_is_harmed',
                'connection_to_central_conflict', 'role_in_key_events', 'contribution_to_themes',
            ], []),
            self::objectLists('systems.examples_of_usage', [
                'system_name', 'everyday_usage', 'combat_or_conflict_usage', 'cultural_or_ceremonial_usage',
                'powerful_or_rare_usage', 'misuse_or_forbidden_usage',
            ], []),
        );
    }

    private static function step8Rules(): array
    {
        return array_merge(
            ['timeline' => ['required', 'array']],
            self::objectLists('timeline.major_historical_events', [
                'name', 'date_or_era', 'description', 'causes', 'consequences', 'historical_importance',
            ], []),
            self::objectLists('timeline.past_events', [
                'name', 'date_or_era', 'description', 'relationship_to_current_tensions', 'connection_to_story',
            ], []),
            self::objectLists('timeline.present_events', [
                'name', 'current_situation', 'driving_forces', 'ongoing_conflicts',
                'connection_to_inciting_event',
            ], []),
            self::objectLists('timeline.future_events', [
                'name', 'projected_course', 'relationship_to_climax_direction',
                'relationship_to_resolution_direction',
            ], ['possible_outcomes']),
            self::objectLists('timeline.key_turning_points', [
                'name', 'when_it_occurs', 'what_changes', 'immediate_effects', 'long_term_effects',
                'connection_to_characters',
            ], []),
            self::strings(['timeline.timeline_summary']),
        );
    }

    private static function step9Rules(): array
    {
        return array_merge(
            ['story_structure' => ['required', 'array']],
            ['story_structure.climax' => ['required', 'array']],
            self::strings([
                'story_structure.overall_story_structure', 'story_structure.structure_type',
                'story_structure.resolution', 'story_structure.resolution_direction',
            ]),
            self::objectLists('story_structure.act_breakdown', [
                'act', 'act_title', 'act_purpose',
            ], ['key_developments']),
            self::objectLists('story_structure.story_arcs', [
                'arc_name', 'arc_type', 'arc_development', 'arc_resolution',
            ], []),
            self::objectLists('story_structure.main_plot_progression', [
                'stage', 'progression', 'function_in_story',
            ], []),
            self::objectLists('story_structure.key_story_points', [
                'story_point', 'story_point_type', 'where_it_occurs', 'narrative_impact',
            ], []),
            self::lists(['story_structure.rising_action']),
            self::strings([
                'story_structure.climax.climax_point', 'story_structure.climax.climax_event',
                'story_structure.climax.stakes_at_climax', 'story_structure.climax.climax_impact',
            ]),
            self::lists(['story_structure.climax.main_characters_involved']),
            self::objectLists('story_structure.high_level_chapter_outline', [
                'chapter', 'chapter_title', 'chapter_purpose', 'story_structure_placement',
            ], ['key_events']),
        );
    }

    private static function step10Rules(): array
    {
        return array_merge(
            ['twists_and_foreshadowing' => ['required', 'array']],
            self::objectLists('twists_and_foreshadowing.major_twists', [
                'twist_name', 'twist_description', 'twist_type', 'reveal_timing', 'story_impact',
            ], ['affected_characters']),
            self::objectLists('twists_and_foreshadowing.clues', [
                'clue', 'clue_type', 'planted_where', 'planted_when', 'connects_to', 'how_hidden',
            ], []),
            self::objectLists('twists_and_foreshadowing.hidden_information', [
                'hidden_information', 'who_knows', 'who_does_not_know', 'why_hidden', 'when_relevant',
            ], []),
            self::lists(['twists_and_foreshadowing.secrets']),
            self::objectLists('twists_and_foreshadowing.mysteries', [
                'mystery', 'mystery_established', 'unresolved_until', 'resolution_connection',
            ], []),
            self::objectLists('twists_and_foreshadowing.foreshadowing_elements', [
                'foreshadowed_element', 'planted_where', 'how_presented', 'prepares', 'narrative_effect',
            ], []),
            self::objectLists('twists_and_foreshadowing.misdirection_elements', [
                'false_impression', 'how_presented', 'reader_belief', 'true_reveal',
            ], []),
            self::objectLists('twists_and_foreshadowing.reveal_timing', [
                'revealed_connection', 'reveal_time', 'reveal_context', 'reader_effect',
            ], []),
            self::lists(['twists_and_foreshadowing.connection_with_story']),
        );
    }

    private static function step11Rules(): array
    {
        return array_merge(
            ['scene_plans' => ['required', 'array', 'min:1']],
            self::strings([
                'scene_plans.*.scene_number', 'scene_plans.*.scene_title', 'scene_plans.*.story_position',
                'scene_plans.*.chapter', 'scene_plans.*.location', 'scene_plans.*.time_period',
                'scene_plans.*.scene_purpose', 'scene_plans.*.emotional_direction',
            ]),
            self::lists([
                'scene_plans.*.scene_objectives', 'scene_plans.*.key_events', 'scene_plans.*.involved_characters',
            ]),
        );
    }

    private static function step12Rules(): array
    {
        return array_merge(
            ['dialogue_plans' => ['required', 'array', 'min:1']],
            self::strings([
                'dialogue_plans.*.scene_reference', 'dialogue_plans.*.conversation_context',
                'dialogue_plans.*.dialogue_purpose', 'dialogue_plans.*.emotional_direction',
                'dialogue_plans.*.relationship_development',
            ]),
            self::lists([
                'dialogue_plans.*.involved_characters', 'dialogue_plans.*.emotional_beats',
            ]),
            self::objectLists('dialogue_plans.*.dialogue', ['character', 'line'], []),
            ['dialogue_plans.*.character_voice_and_tone' => ['required', 'array']],
        );
    }

    private static function step13Rules(): array
    {
        return array_merge(
            ['chapter_plan' => ['required', 'array', 'min:1']],
            self::integers(['chapter_plan.*.chapter_number']),
            self::strings([
                'chapter_plan.*.title', 'chapter_plan.*.summary', 'chapter_plan.*.pacing_and_flow',
            ]),
            self::lists(['chapter_plan.*.scenes', 'chapter_plan.*.chapter_goals'], true),
        );
    }

    private static function step14Rules(): array
    {
        return array_merge(
            ['page_plan' => ['required', 'array', 'min:1']],
            self::integers([
                'page_plan.*.page_number', 'page_plan.*.chapter_reference', 'page_plan.*.estimated_word_count',
            ]),
            self::strings(['page_plan.*.content_summary']),
            self::lists(['page_plan.*.scenes_covered', 'page_plan.*.key_points'], true),
        );
    }

    private static function step15Rules(): array
    {
        return array_merge(
            self::integers(['chapter_number']),
            self::strings(['chapter_title']),
            ['chapter_summary' => ['required', 'array']],
            self::strings([
                'chapter_summary.chapter_purpose', 'chapter_summary.progression',
                'chapter_summary.emotional_and_narrative_movement', 'chapter_summary.chapter_ending_and_setup',
            ]),
            self::lists([
                'chapter_summary.chapter_goals', 'chapter_summary.characters_involved',
                'chapter_summary.important_events', 'chapter_summary.relevant_conflicts',
                'chapter_summary.important_revelations', 'chapter_summary.scene_progression',
                'chapter_summary.continuity_requirements',
            ], true),
        );
    }
}
