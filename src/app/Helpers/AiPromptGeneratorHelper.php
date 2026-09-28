<?php

namespace App\Helpers;

class AiPromptGeneratorHelper
{
    public const AI_PROMPT_NAME_FOUNDATION = 'Foundation';
    public const AI_PROMPT_NAME_PLAN_CHAPTER = 'Plan Chapter';
    public const AI_PROMPT_NAME_CHAPTER_CONTENT = 'Chapter Content';

    public static function foundationPrompt(): string
    {
        $prompt = "
            You are a professional novel development AI.

            Your task is to develop the complete story and world package of a novel: its narrative foundation, its cast, and the world that surrounds them.

            ==================================================
            OVERALL OBJECTIVE
            ==================================================

            You are performing the equivalent of an eight phase professional novel development pipeline inside this single request.

            The phases are not separate documents. They are one continuous creative process in which each phase is derived from the result of the phase before it.

            Execute them internally, in the order given, and return one single JSON object that contains the result of every phase.

            The result of this request becomes the reference material that later requests use to plan and write the novel. It must therefore be internally consistent, specific, and complete.

            ==================================================
            INPUT DATA
            ==================================================

            LANGUAGE:

            {{language}}

            GENRE REQUIREMENT:

            {{genre_prompt_instruction}}

            NOVEL TYPE REQUIREMENT:

            {{novel_type_instruction}}

            TARGET AUDIENCE REQUIREMENT:

            {{audience_instruction}}

            ADDITIONAL NOVEL INFORMATION:

            {{additional_information}}

            ==================================================
            IMPORTANT GLOBAL RULES
            ==================================================

            1. Run the phases below in order. A later phase must consume the result of the earlier phases instead of reinventing it.

            2. Everything you decide in a phase is the source of truth for every later phase. Never contradict, rename, or silently replace a fact, a name, a place, a rule, or a motivation that an earlier phase already established.

            3. Integrate the genre requirement, the novel type requirement, the target audience requirement, and the additional novel information into every phase that they affect. When several genres are selected, combine them into one unified story direction instead of treating them as separate story elements. When no additional information exists, use creative judgement to improve originality, depth, and storytelling quality.

            4. Character information in the foundation phase establishes only the direction required for the story. The full cast is produced in the character phase. World information in the foundation phase establishes only the context required for the story. The world bible is produced in the world phase.

            5. Maintain clear cause-and-effect progression. Every major event must have a meaningful relationship with the characters, the conflict, the stakes, or the themes.

            6. Avoid generic, predictable, formulaic, repetitive, mechanical, or shallow storytelling. Do not rely on common formulas unless they are meaningfully transformed into something distinctive.

            7. Generate every string value in the requested language, with natural vocabulary, grammar, tone, cultural expression, and writing style appropriate for that language.

            8. Every phase must stay inside its own scope. Respect the scope limits listed with each phase.

            ==================================================
            LOGICAL GENERATION PIPELINE
            ==================================================

            PHASE 1 - STORY FOUNDATION
            ------------------------------------------

            Responsibility:

                1. Novel Title
                2. Novel Subtitle
                3. Novel Foundation

            The foundation must establish:

                - Core story concept
                - Premise
                - Narrative hook
                - Central question
                - Central theme
                - Emotional direction
                - Setting direction
                - Main character direction
                - Important character roles
                - Central motivation
                - Central goal
                - Character journey direction
                - Central conflict
                - Opposing force
                - Stakes
                - Consequences
                - Important events
                - Turning points
                - Escalation
                - Climax direction
                - Resolution direction
                - Major themes

            The foundation must determine:

                - What makes the novel distinctive.
                - Why readers should care about the story.
                - What the main character wants, and why.
                - What prevents the goal.
                - What is at stake.
                - What consequences result from failure.
                - How the central conflict develops.
                - How complications increase.
                - How important discoveries affect the story.
                - How turning points change the direction of the narrative.
                - How the story escalates toward the climax.
                - What climax direction naturally fits the story.
                - What resolution direction continues the story's themes and character journey meaningfully.

            Scope limits:

                - Do not create complete character profiles or a character database.
                - Do not create world-building documentation or a world bible.

            PHASE 2 - CHARACTERS
            ------------------------------------------

            Consumes: the story foundation from phase 1.

            Responsibility:

                1. Main Character
                2. Supporting Characters
                3. Opposing Characters
                4. Character Relationships
                5. Character Development Direction

            Every character must have:

                - Clear narrative purpose
                - Role in the story
                - Motivation
                - Goal
                - Personality direction
                - Strengths
                - Weaknesses
                - Internal conflict
                - External conflict
                - Relationship purpose
                - Character development direction

            The main character must establish:

                - Identity direction
                - Role in the story
                - Core motivation
                - Main goal
                - Personal conflict
                - Emotional struggle
                - Character flaw or limitation
                - Growth direction
                - Connection with the central conflict

            Supporting characters must establish:

                - Their purpose in the narrative
                - Relationship with the protagonist
                - Contribution to conflict
                - Contribution to themes
                - Emotional or narrative importance

            Opposing characters must establish:

                - Identity direction
                - Goal
                - Motivation
                - Method of opposition
                - Conflict with the protagonist
                - Narrative importance

            Scope limits:

                - Do not create complete biographies, childhood histories, family trees, or detailed life timelines.

            PHASE 3 - WORLD BIBLE
            ------------------------------------------

            Consumes: the story foundation from phase 1 and the characters from phase 2.

            Responsibility:

                1. World Overview
                2. History and Lore
                3. Cultures and Societies
                4. Rules and Systems
                5. Key World Elements

            The world overview must define:

                - World name and nature
                - Type of world and its scale
                - Physical structure and geography direction
                - Dominant environment
                - State of the world at the start of the story
                - Relationship between the world and the story conflict
                - General tone and atmosphere of the world

            The history and lore must include:

                - Significant historical eras
                - Major historical events
                - Ancient legends or myths
                - Origin stories relevant to the world
                - Key turning points in world history
                - How history shaped current tensions
                - Historical connections to the story conflict
                - Secrets or forgotten knowledge

            The cultures and societies must include:

                - Major cultures
                - Social structures
                - Customs and traditions
                - Beliefs and values
                - Language and communication direction
                - Arts, education, and daily life
                - Class systems and power distribution
                - Cultural tensions
                - Connection between cultures and the characters

            The rules and systems must include:

                - Magic, technology, or supernatural systems
                - Sources of power and their costs
                - Limitations and consequences
                - Governing laws and order systems
                - Economy and trade systems
                - Political systems
                - Any system that affects the characters and conflict

            The key world elements must include:

                - Unique features of the world
                - Important artifacts or objects
                - Significant natural or supernatural phenomena
                - Centers of power
                - Important institutions or organizations
                - Elements that directly influence the story
                - Elements connected to character goals

            The world must be internally consistent, and the characters' goals, conflicts, origins, and relationships must feel naturally rooted in it.

            Scope limits:

                - Do not create complete location databases, detailed maps, faction structures, creature databases, or a chronology. Those are produced by later phases of this request.

            PHASE 4 - LOCATIONS
            ------------------------------------------

            Consumes: the world bible from phase 3 and the characters from phase 2.

            Responsibility:

                1. Major Locations
                2. Cities and Regions
                3. Important Places
                4. Environment Details
                5. Location Significance

            Major locations must define:

                - Name and type of location
                - Geographic position
                - General description and atmosphere
                - Importance to the world
                - Connection to the story conflict

            Cities and regions must define:

                - City or region name
                - Population and culture direction
                - Layout and architecture direction
                - Economy and governance
                - Social atmosphere
                - Connection to the characters

            Important places must define:

                - Place name and type
                - Location within the world
                - Purpose and function
                - Description and atmosphere
                - Significance to the story
                - Presence in key scenes or events

            Environment details must include:

                - Climate and weather direction
                - Terrain and natural features
                - Flora and fauna direction
                - Unique environmental characteristics
                - How the environment affects daily life and travel
                - Environmental connection to the conflict

            Location significance must define, for each location:

                - Role in the narrative
                - Connection to character goals
                - Connection to the central conflict
                - Events likely to occur there
                - Emotional or thematic importance

            Locations must be places the characters can naturally inhabit, travel through, and interact with, and they must not contradict the world bible.

            Scope limits:

                - Do not create maps, or descriptions of locations that do not serve the story.

            PHASE 5 - FACTIONS AND ORGANIZATIONS
            ------------------------------------------

            Consumes: the world bible from phase 3 and the locations from phase 4.

            Responsibility:

                1. Factions
                2. Organizations
                3. Ideologies and Goals
                4. Key Members
                5. Relationships and Conflicts
                6. Influence in Story

            Each faction must define:

                - Faction name
                - Faction type
                - Purpose and reason for existing
                - Structure and hierarchy
                - Influence and reach
                - Connection to the story conflict

            Each organization must define:

                - Organization name
                - Organization type
                - Mission and function
                - Membership direction
                - Resources and power
                - Connection to the factions and story

            Ideologies and goals must define, for each faction:

                - Core ideology
                - Values and beliefs
                - Ultimate goals
                - Methods used to achieve goals
                - Boundaries and limits
                - How ideology drives their actions

            Key members must define:

                - Member name
                - Position within the faction
                - Role and responsibility
                - Motivation and personal goals
                - Relationship to the main characters
                - Narrative importance

            Relationships and conflicts must define:

                - Alliances between factions
                - Rivalries and enmities
                - Areas of cooperation
                - Sources of conflict
                - Historical grievances
                - How these dynamics affect the story

            Influence in story must define:

                - Role of each faction in the central conflict
                - How factions affect the main characters
                - How factions influence key events
                - How their influence changes as the story progresses
                - Their contribution to themes

            Factions must emerge naturally from the established world and must operate naturally within the established locations.

            PHASE 6 - CREATURES AND BEINGS
            ------------------------------------------

            Consumes: the world bible from phase 3, the locations from phase 4, and the factions from phase 5.

            Responsibility:

                1. Creature and Species List
                2. Traits and Abilities
                3. Behavior and Ecology
                4. Role in World and Story
                5. Visual Descriptions

            The creature and species list must define:

                - Name
                - Species or being type
                - Classification
                - Where it is found
                - Purpose in the world

            Traits and abilities must define, for each creature:

                - Physical traits
                - Natural abilities
                - Special powers or features
                - Strengths
                - Weaknesses
                - Limitations

            Behavior and ecology must define, for each creature:

                - Behavioral patterns
                - Diet and survival
                - Habitat and territory
                - Reproduction or propagation
                - Social structure
                - Relationship with the environment
                - Relationship with other creatures

            Role in world and story must define, for each creature:

                - Role within the world
                - Connection to cultures and factions
                - Connection to the central conflict
                - Presence in key events
                - Contribution to themes
                - Narrative importance

            Visual descriptions must define, for each creature:

                - Overall appearance
                - Size and silhouette
                - Coloring and texture
                - Distinctive markings
                - Movement and mannerisms
                - Sensory presence

            Creatures must belong naturally in the established world, inhabit the established environments, and connect meaningfully with the factions that use, fear, protect, or oppose them.

            Scope limits:

                - Do not create creatures that do not serve the story.

            PHASE 7 - WORLD SYSTEMS
            ------------------------------------------

            Consumes: the world bible from phase 3, the creatures from phase 6, and the factions from phase 5.

            Responsibility:

                1. System Types and Rules
                2. Mechanics and Limitations
                3. Effect on Society and Story
                4. Examples of Usage

            System types and rules must define:

                - Name
                - Type
                - Core purpose
                - Source of power or function
                - Scope of the system
                - Fundamental rules

            Mechanics and limitations must define, for each system:

                - How it is used
                - Conditions and requirements
                - Costs and consequences
                - Restrictions and limits
                - Balance and fairness
                - Failure conditions

            Effect on society and story must define, for each system:

                - Impact on daily life
                - Impact on culture and institutions
                - Impact on economy and politics
                - Who benefits and who is harmed
                - Connection to the central conflict
                - Role in key events
                - Contribution to themes

            Examples of usage must define, for each system:

                - Everyday usage
                - Combat or conflict usage
                - Cultural or ceremonial usage
                - Powerful or rare usage
                - Misuse or forbidden usage

            Systems must be consistent with the established world, must interact meaningfully with the creatures and their abilities, and must be used, controlled, or struggled over by the factions.

            Scope limits:

                - Do not invent system rules beyond what the story needs.

            PHASE 8 - WORLD TIMELINE
            ------------------------------------------

            Consumes: the world bible from phase 3, the factions from phase 5, and the story foundation from phase 1.

            Responsibility:

                1. Major Historical Events
                2. Past Events
                3. Present Events
                4. Future Events
                5. Key Turning Points
                6. Timeline Summary

            Major historical events must define:

                - Name
                - Date or era
                - Description
                - Causes
                - Consequences
                - Historical importance

            Past events must define:

                - Name
                - Date or era
                - Description
                - Relationship to current tensions
                - Connection to the story

            Present events must define:

                - Name
                - Current situation
                - Driving forces
                - Ongoing conflicts
                - Connection to the inciting event

            Future events must define:

                - Name
                - Projected course
                - Relationship to the climax direction
                - Relationship to the resolution direction
                - Possible outcomes

            Key turning points must define:

                - Name
                - When it occurs
                - What changes
                - Immediate effects
                - Long-term effects
                - Connection to characters

            The timeline summary must give a clear chronological overview.

            The timeline must emerge naturally from the established world history, must include the rise, fall, and interactions of the factions, and must connect the story foundation events to the larger world chronology.

            Scope limits:

                - Do not create the plot structure, the scene breakdown, the chapter plan, or any prose.

            ==================================================
            DEPENDENCY RULES
            ==================================================

            The following dependencies are mandatory. They are the reason the phases must be executed in order.

                Phase 2 must be built on the foundation established in phase 1.
                Phase 3 must be built on the foundation from phase 1 and the characters from phase 2, and must root the characters' origins and goals in the world.
                Phase 4 must exist inside the world of phase 3 and must host the characters of phase 2.
                Phase 5 must emerge from the world of phase 3 and must operate inside the locations of phase 4.
                Phase 6 must belong to the world of phase 3, inhabit the locations of phase 4, and relate to the factions of phase 5.
                Phase 7 must be consistent with the world of phase 3 and must interact with the creatures of phase 6 and the factions of phase 5.
                Phase 8 must extend the world history of phase 3, cover the rise and fall of the factions of phase 5, and align with the foundation events of phase 1.

            Where a later phase adds a detail, the detail must extend the earlier phase instead of replacing it. A name, a place, a rule, an organisation, or a motivation introduced in an earlier phase must reappear unchanged wherever it is referenced later.

            ==================================================
            FINAL OUTPUT REQUIREMENTS
            ==================================================

            Return one single JSON object that contains the result of all eight phases.

            Do not split the phases across separate responses.

            Do not return the phases one at a time.

            Do not merge two phases into one field, and do not rename a field.

            ==================================================
            JSON STRUCTURE
            ==================================================

            Return ONLY valid JSON.

            {
                \"title\": \"\",
                \"sub_title\": \"\",
                \"novel_foundation\": {
                    \"premise\": \"\",
                    \"story_concept\": \"\",
                    \"narrative_hook\": \"\",
                    \"central_question\": \"\",
                    \"central_theme\": \"\",
                    \"emotional_direction\": \"\",
                    \"setting\": \"\",
                    \"main_character_direction\": \"\",
                    \"important_character_roles\": [],
                    \"central_motivation\": \"\",
                    \"central_goal\": \"\",
                    \"character_journey_direction\": \"\",
                    \"central_conflict\": \"\",
                    \"opposing_force\": \"\",
                    \"stakes\": \"\",
                    \"consequences\": \"\",
                    \"opening_situation\": \"\",
                    \"inciting_event\": \"\",
                    \"initial_goal\": \"\",
                    \"major_complications\": [],
                    \"discoveries\": [],
                    \"turning_points\": [],
                    \"escalation\": \"\",
                    \"climax_direction\": \"\",
                    \"resolution_direction\": \"\",
                    \"themes\": []
                },
                \"characters\": {
                    \"characters\": [
                        {
                            \"name\": \"\",
                            \"role\": \"\",
                            \"character_type\": \"\",
                            \"personality\": \"\",
                            \"appearance_direction\": \"\",
                            \"background_direction\": \"\",
                            \"motivation\": \"\",
                            \"goal\": \"\",
                            \"strengths\": [],
                            \"weaknesses\": [],
                            \"internal_conflict\": \"\",
                            \"external_conflict\": \"\",
                            \"relationship_to_main_character\": \"\",
                            \"character_arc_direction\": \"\"
                        }
                    ],
                    \"relationships\": [
                        {
                            \"characters\": \"\",
                            \"relationship\": \"\",
                            \"story_purpose\": \"\"
                        }
                    ]
                },
                \"world_bible\": {
                    \"world_overview\": {
                        \"world_name_and_nature\": \"\",
                        \"world_scale\": \"\",
                        \"geography_direction\": \"\",
                        \"dominant_environment\": \"\",
                        \"world_state_at_story_start\": \"\",
                        \"connection_to_story_conflict\": \"\",
                        \"tone_and_atmosphere\": \"\"
                    },
                    \"history_and_lore\": {
                        \"significant_eras\": [],
                        \"major_historical_events\": [],
                        \"legends_and_myths\": [],
                        \"origin_stories\": [],
                        \"world_turning_points\": [],
                        \"how_history_shaped_current_tensions\": \"\",
                        \"historical_connection_to_story_conflict\": \"\",
                        \"secrets_and_forgotten_knowledge\": []
                    },
                    \"cultures_and_societies\": [
                        {
                            \"name\": \"\",
                            \"social_structure\": \"\",
                            \"customs_and_traditions\": [],
                            \"beliefs_and_values\": [],
                            \"daily_life\": \"\",
                            \"class_system_and_power\": \"\",
                            \"relationship_to_story\": \"\"
                        }
                    ],
                    \"rules_and_systems\": [
                        {
                            \"system_name\": \"\",
                            \"system_type\": \"\",
                            \"source_of_power\": \"\",
                            \"costs_and_limitations\": \"\",
                            \"governing_laws_and_order\": \"\",
                            \"impact_on_story\": \"\"
                        }
                    ],
                    \"key_world_elements\": [
                        {
                            \"element_name\": \"\",
                            \"element_description\": \"\",
                            \"significance\": \"\",
                            \"connection_to_characters\": \"\",
                            \"story_influence\": \"\"
                        }
                    ]
                },
                \"locations\": {
                    \"major_locations\": [
                        {
                            \"name\": \"\",
                            \"type\": \"\",
                            \"geographic_position\": \"\",
                            \"description_and_atmosphere\": \"\",
                            \"importance_to_world\": \"\",
                            \"connection_to_story_conflict\": \"\"
                        }
                    ],
                    \"cities_and_regions\": [
                        {
                            \"name\": \"\",
                            \"population_and_culture\": \"\",
                            \"layout_and_architecture\": \"\",
                            \"economy_and_governance\": \"\",
                            \"social_atmosphere\": \"\",
                            \"connection_to_characters\": \"\"
                        }
                    ],
                    \"important_places\": [
                        {
                            \"name\": \"\",
                            \"type\": \"\",
                            \"location_in_world\": \"\",
                            \"purpose_and_function\": \"\",
                            \"description_and_atmosphere\": \"\",
                            \"significance_to_story\": \"\"
                        }
                    ],
                    \"environment_details\": {
                        \"climate_and_weather\": \"\",
                        \"terrain_and_natural_features\": \"\",
                        \"flora_and_fauna\": \"\",
                        \"unique_characteristics\": \"\",
                        \"effect_on_daily_life_and_travel\": \"\",
                        \"connection_to_conflict\": \"\"
                    },
                    \"location_significance\": [
                        {
                            \"location_name\": \"\",
                            \"role_in_narrative\": \"\",
                            \"connection_to_character_goals\": \"\",
                            \"connection_to_central_conflict\": \"\",
                            \"likely_events\": [],
                            \"emotional_or_thematic_importance\": \"\"
                        }
                    ]
                },
                \"factions\": {
                    \"factions\": [
                        {
                            \"name\": \"\",
                            \"type\": \"\",
                            \"purpose\": \"\",
                            \"structure_and_hierarchy\": \"\",
                            \"influence_and_reach\": \"\",
                            \"connection_to_story_conflict\": \"\"
                        }
                    ],
                    \"organizations\": [
                        {
                            \"name\": \"\",
                            \"type\": \"\",
                            \"mission_and_function\": \"\",
                            \"membership_direction\": \"\",
                            \"resources_and_power\": \"\",
                            \"connection_to_story\": \"\"
                        }
                    ],
                    \"ideologies_and_goals\": [
                        {
                            \"faction_name\": \"\",
                            \"core_ideology\": \"\",
                            \"values_and_beliefs\": [],
                            \"ultimate_goals\": [],
                            \"methods_and_limitations\": \"\",
                            \"how_ideology_drives_actions\": \"\"
                        }
                    ],
                    \"key_members\": [
                        {
                            \"name\": \"\",
                            \"faction_name\": \"\",
                            \"position\": \"\",
                            \"role_and_responsibility\": \"\",
                            \"motivation_and_personal_goals\": \"\",
                            \"relationship_to_main_characters\": \"\",
                            \"narrative_importance\": \"\"
                        }
                    ],
                    \"relationships_and_conflicts\": [
                        {
                            \"factions_involved\": \"\",
                            \"relationship_type\": \"\",
                            \"alliance_details\": \"\",
                            \"source_of_conflict\": \"\",
                            \"historical_grievances\": \"\",
                            \"effect_on_story\": \"\"
                        }
                    ],
                    \"influence_in_story\": [
                        {
                            \"faction_name\": \"\",
                            \"role_in_central_conflict\": \"\",
                            \"effect_on_main_characters\": \"\",
                            \"influence_on_key_events\": \"\",
                            \"progression_of_influence\": \"\",
                            \"contribution_to_themes\": \"\"
                        }
                    ]
                },
                \"creatures\": {
                    \"creatures_and_species\": [
                        {
                            \"name\": \"\",
                            \"species_or_being_type\": \"\",
                            \"classification\": \"\",
                            \"found_in\": \"\",
                            \"purpose_in_world\": \"\"
                        }
                    ],
                    \"traits_and_abilities\": [
                        {
                            \"creature_name\": \"\",
                            \"physical_traits\": [],
                            \"natural_abilities\": [],
                            \"special_powers_or_features\": [],
                            \"strengths\": [],
                            \"weaknesses\": [],
                            \"limitations\": []
                        }
                    ],
                    \"behavior_and_ecology\": [
                        {
                            \"creature_name\": \"\",
                            \"behavioral_patterns\": \"\",
                            \"diet_and_survival\": \"\",
                            \"habitat_and_territory\": \"\",
                            \"reproduction_or_propagation\": \"\",
                            \"social_structure\": \"\",
                            \"relationship_with_environment\": \"\",
                            \"relationship_with_other_creatures\": \"\"
                        }
                    ],
                    \"role_in_world_and_story\": [
                        {
                            \"creature_name\": \"\",
                            \"role_within_world\": \"\",
                            \"connection_to_cultures_and_factions\": \"\",
                            \"connection_to_central_conflict\": \"\",
                            \"presence_in_key_events\": \"\",
                            \"contribution_to_themes\": \"\",
                            \"narrative_importance\": \"\"
                        }
                    ],
                    \"visual_descriptions\": [
                        {
                            \"creature_name\": \"\",
                            \"overall_appearance\": \"\",
                            \"size_and_silhouette\": \"\",
                            \"coloring_and_texture\": \"\",
                            \"distinctive_markings\": \"\",
                            \"movement_and_mannerisms\": \"\",
                            \"sensory_presence\": \"\"
                        }
                    ]
                },
                \"systems\": {
                    \"system_types_and_rules\": [
                        {
                            \"name\": \"\",
                            \"type\": \"\",
                            \"core_purpose\": \"\",
                            \"source_of_power_or_function\": \"\",
                            \"scope_of_system\": \"\",
                            \"fundamental_rules\": []
                        }
                    ],
                    \"mechanics_and_limitations\": [
                        {
                            \"system_name\": \"\",
                            \"how_it_is_used\": \"\",
                            \"conditions_and_requirements\": \"\",
                            \"costs_and_consequences\": \"\",
                            \"restrictions_and_limits\": \"\",
                            \"balance_and_fairness\": \"\",
                            \"failure_conditions\": \"\"
                        }
                    ],
                    \"effect_on_society_and_story\": [
                        {
                            \"system_name\": \"\",
                            \"impact_on_daily_life\": \"\",
                            \"impact_on_culture_and_institutions\": \"\",
                            \"impact_on_economy_and_politics\": \"\",
                            \"who_benefits_and_who_is_harmed\": \"\",
                            \"connection_to_central_conflict\": \"\",
                            \"role_in_key_events\": \"\",
                            \"contribution_to_themes\": \"\"
                        }
                    ],
                    \"examples_of_usage\": [
                        {
                            \"system_name\": \"\",
                            \"everyday_usage\": \"\",
                            \"combat_or_conflict_usage\": \"\",
                            \"cultural_or_ceremonial_usage\": \"\",
                            \"powerful_or_rare_usage\": \"\",
                            \"misuse_or_forbidden_usage\": \"\"
                        }
                    ]
                },
                \"timeline\": {
                    \"major_historical_events\": [
                        {
                            \"name\": \"\",
                            \"date_or_era\": \"\",
                            \"description\": \"\",
                            \"causes\": \"\",
                            \"consequences\": \"\",
                            \"historical_importance\": \"\"
                        }
                    ],
                    \"past_events\": [
                        {
                            \"name\": \"\",
                            \"date_or_era\": \"\",
                            \"description\": \"\",
                            \"relationship_to_current_tensions\": \"\",
                            \"connection_to_story\": \"\"
                        }
                    ],
                    \"present_events\": [
                        {
                            \"name\": \"\",
                            \"current_situation\": \"\",
                            \"driving_forces\": \"\",
                            \"ongoing_conflicts\": \"\",
                            \"connection_to_inciting_event\": \"\"
                        }
                    ],
                    \"future_events\": [
                        {
                            \"name\": \"\",
                            \"projected_course\": \"\",
                            \"relationship_to_climax_direction\": \"\",
                            \"relationship_to_resolution_direction\": \"\",
                            \"possible_outcomes\": []
                        }
                    ],
                    \"key_turning_points\": [
                        {
                            \"name\": \"\",
                            \"when_it_occurs\": \"\",
                            \"what_changes\": \"\",
                            \"immediate_effects\": \"\",
                            \"long_term_effects\": \"\",
                            \"connection_to_characters\": \"\"
                        }
                    ],
                    \"timeline_summary\": \"\"
                }
            }

            ==================================================
            VALIDATION REQUIREMENTS
            ==================================================

            Before returning the result, verify:

                - All eight phases are present in the response.
                - The title represents the novel's identity and the subtitle supports it.
                - The premise is distinctive and the foundation has a clear narrative direction.
                - The protagonist has a meaningful motivation and goal, the central conflict is meaningful, and the opposing force creates genuine obstacles.
                - Stakes and consequences are clear, major complications logically develop the conflict, and discoveries and turning points meaningfully affect the story.
                - Escalation leads naturally toward the climax and the resolution direction fits the story.
                - Themes are connected to the narrative.
                - The main character supports the central conflict, supporting characters have clear narrative purposes, and opposing characters create meaningful obstacles.
                - Character motivations are believable, relationships support the story, and character arcs connect with themes.
                - No unnecessary characters, biographies, locations, factions, creatures, or systems are created.
                - The world is internally consistent, cultures connect naturally with the characters, and rules and systems are clear and consistent.
                - No location contradicts the established world, and locations are ones the characters can inhabit.
                - Factions emerge from the world, operate in the locations, and do not contradict the world bible.
                - Creatures belong in the world, inhabit the locations, and relate to the factions.
                - Systems are consistent with the world and connect to creatures and factions.
                - The timeline extends the world history, covers the factions, and aligns with the foundation.
                - The story feels original and professionally developed rather than generic, mechanical, or formulaic.
                - Genre, novel type, audience, and additional novel information requirements are properly integrated.
                - The requested language is respected in every value.
                - The response is valid JSON only, with no explanation, markdown, or additional text outside the JSON.
        " . self::jsonOutputContract();

        return $prompt;
    }

    public static function planChapterPrompt(): string
    {
        $prompt = "
            You are a professional novel planning AI.

            Your task is to turn an already developed story and world package into the complete narrative plan of a novel: its structure, its hidden layers, its scenes, its chapters, its pages, and the writing blueprint of every chapter.

            ==================================================
            OVERALL OBJECTIVE
            ==================================================

            You are performing the equivalent of a seven phase professional novel planning pipeline inside this single request.

            The phases are not separate documents. They are one continuous planning process in which each phase is derived from the result of the phase before it.

            Execute them internally, in the order given, and return one single JSON object that contains the result of every phase.

            The result of this request becomes the authoritative plan that a later request uses to write the prose of each chapter. It must therefore be internally consistent, specific, and complete.

            ==================================================
            INPUT DATA
            ==================================================

            The story and world package below is already final. Treat it as the source of truth. Analyse it carefully, build on it, and never change, rewrite, or expand it.

            NOVEL FOUNDATION:

            {{foundation}}

            CHARACTERS:

            {{characters}}

            WORLD BIBLE:

            {{world_bible}}

            LOCATIONS:

            {{locations}}

            FACTIONS:

            {{factions}}

            CREATURES:

            {{creatures}}

            SYSTEMS:

            {{systems}}

            TIMELINE:

            {{timeline}}

            ==================================================
            IMPORTANT GLOBAL RULES
            ==================================================

            1. Run the phases below in order. A later phase must consume the result of the earlier phases instead of reinventing it.

            2. Everything you decide in a phase is the source of truth for every later phase. Never contradict, rename, or silently replace a character, a location, a faction, a creature, a system, a rule, a motive, or a date that an earlier phase or the input package already established.

            3. The input package is read-only. If the plan needs a detail the package does not contain, introduce it as an addition that fits the package. Never rewrite the package to make the plan easier.

            4. Generate every string value in the requested language, with natural vocabulary, grammar, tone, cultural expression, and writing style appropriate for that language.

            5. Avoid generic, predictable, formulaic, repetitive, mechanical, or shallow planning. Every element must be specific, consequential, and connected.

            6. Every phase must stay inside its own scope. Never write prose chapters.

            ==================================================
            LOGICAL GENERATION PIPELINE
            ==================================================

            PHASE 1 - STORY STRUCTURE
            ------------------------------------------

            Consumes: the foundation, the characters, the world bible, and the timeline from the input package.

            Responsibility:

                1. Structure Type Selection
                2. Overall Story Structure
                3. Act and Arc Breakdown
                4. Main Plot Progression
                5. Key Story Points
                6. Rising Action
                7. Climax
                8. Resolution
                9. High Level Chapter Outline

            Select a single high-level structure type for the novel. If the story requires a distinctive shape, use a custom structure and explain how it works.

            The overall story structure must define:

                - Overall story structure
                - Structure type
                - Resolution
                - Resolution direction

            The act and arc breakdown must define, for each act:

                - Act
                - Act title
                - Act purpose
                - Key developments

            The story arcs must define:

                - Arc name
                - Arc type
                - Arc development
                - Arc resolution

            The main plot progression must define:

                - Stage
                - Progression
                - Function in the story

            The key story points must define:

                - Story point
                - Story point type
                - Where it occurs
                - Narrative impact

            Rising action must show how tension builds before the climax.

            The climax must define:

                - Climax point
                - Climax event
                - Stakes at climax
                - Main characters involved
                - Climax impact

            The resolution must define how the story concludes.

            The high level chapter outline must define, for each chapter:

                - Chapter
                - Chapter title
                - Chapter purpose
                - Key events
                - Story structure placement

            Build the structure so the characters' arcs, conflicts, and turning points fit naturally into the narrative, so the events, acts, and arcs are consistent with the established world, and so the acts, arcs, and chapters align with the established chronology.

            Scope limits:

                - Keep the chapter outline high level. Do not create scene-level planning in this phase, and do not write any prose.

            PHASE 2 - TWISTS AND FORESHADOWING
            ------------------------------------------

            Consumes: the story structure from phase 1, the characters from the input package, and the world bible from the input package.

            Responsibility:

                1. Major Twists
                2. Hidden Clues
                3. Secrets and Hidden Information
                4. Mysteries
                5. Foreshadowing Elements
                6. Misdirection Elements
                7. Reveal Timing
                8. Connection With Story

            Major twists must define:

                - Twist name
                - Twist description
                - Twist type
                - Affected characters
                - Reveal timing
                - Story impact

            Hidden clues must define:

                - Clue
                - Clue type
                - Planted where
                - Planted when
                - Connects to
                - How hidden

            Secrets and hidden information must define:

                - Hidden information
                - Who knows
                - Who does not know
                - Why hidden
                - When relevant

            Mysteries must define:

                - Mystery
                - Mystery established
                - Unresolved until
                - Resolution connection

            Foreshadowing elements must define:

                - Foreshadowed element
                - Planted where
                - How presented
                - Prepares
                - Narrative effect

            Misdirection elements must define:

                - False impression
                - How presented
                - Reader belief
                - True reveal

            Reveal timing must define, for each important realization:

                - Revealed connection
                - Reveal time
                - Reveal context
                - Reader effect

            The connection with the story must state how the hidden elements connect with the overall story.

            Twists must fit naturally into the established structure, must connect meaningfully with the characters, their secrets, and their development, and must be consistent with the established world.

            Scope limits:

                - Do not change the story structure, and do not introduce elements the story structure does not support.

            PHASE 3 - SCENE PLANS
            ------------------------------------------

            Consumes: the story structure from phase 1, the twists and foreshadowing from phase 2, and the locations from the input package.

            Responsibility:

                1. Scene List
                2. Scene Objectives
                3. Key Events per Scene
                4. Involved Characters
                5. Location
                6. Time Period
                7. Scene Purpose
                8. Emotional Direction

            Establish the complete ordered list of scenes that make up the story. Scene order must follow the established story structure and chapter outline. Scene numbering must be sequential.

            Each scene must define:

                - Scene number and title
                - Position within the story
                - Which chapter it belongs to
                - Scene objectives
                - Key events
                - Involved characters
                - Location
                - Time period
                - Scene purpose
                - Emotional direction

            Scene objectives must state the main objective of the scene, the secondary objectives, what the scene must establish or resolve, and how the objective advances the plot.

            Key events must state the sequence of important events, the significant discoveries or revelations, the turning points within the scene, and the consequences that carry into later scenes.

            Involved characters must list only the characters who are essential to the scene, the role each of them plays in it, and the characters whose goals or conflicts are affected.

            Location must assign each scene to an existing location and state how that location influences the scene's events and mood.

            Time period must state the point in the story timeline, any time shifts or gaps, and how the timing affects the scene.

            Scene purpose must state the dramatic purpose of the scene and what it contributes to the characters, the plot, and the themes.

            Emotional direction must state the primary emotion of the scene, how the emotion shifts during the scene, and the emotional state the scene leaves for the reader.

            The revealed clues, the foreshadowing, and the reveals from phase 2 must be planted in the correct scenes. Every scene must be connected to the next through cause and effect.

            Scope limits:

                - Do not create dialogue scripts, full prose for scenes, or full chapter drafts.

            PHASE 4 - DIALOGUE PLANS
            ------------------------------------------

            Consumes: the characters from the input package and the scene plans from phase 3.

            Responsibility: plan only the dialogues that are important to the story and that are directly connected to existing scenes.

            Each dialogue must define:

                - The scene it belongs to, using the scene number of phase 3
                - Involved characters
                - Conversation context
                - Dialogue purpose
                - Emotional direction
                - Character voice and tone
                - Emotional beats
                - The dialogue itself
                - Relationship development

            The dialogue must stay consistent with the established scene objective and emotional direction, must use only characters from the input package, and must develop character relationships in a way that strengthens the story.

            Do not create a dialogue plan for a scene that does not need one.

            Scope limits:

                - Do not create full prose narrative or final novel text.

            PHASE 5 - CHAPTER PLAN
            ------------------------------------------

            Consumes: the scene plans from phase 3 and the story structure from phase 1.

            Responsibility:

                1. Chapter Organization
                2. Chapter Sequence
                3. Chapter Summaries
                4. Scenes inside Each Chapter
                5. Pacing and Flow
                6. Chapter Goals

            Organize every existing scene into a logical chapter sequence. Every scene from phase 3 must belong to exactly one chapter, and every scene reference must match a scene number from phase 3.

            For each chapter:

                - Assign a chapter number. Chapter numbers must start at 1 and increase by exactly 1 with no gaps and no duplicates.
                - Write a chapter title.
                - Write a chapter summary.
                - List the scenes inside the chapter.
                - Define the chapter goals.
                - Describe the pacing and flow.
                - Maintain narrative tension across the chapter sequence.

            Organize the chapters so they faithfully follow the established story structure.

            Scope limits:

                - Do not write prose, and do not write the final novel text.

            PHASE 6 - PAGE PLAN
            ------------------------------------------

            Consumes: the chapter plan from phase 5 and the scene plans from phase 3.

            Responsibility: break every chapter down into a practical page-level writing structure.

            Each page must define:

                - Page number
                - Chapter reference
                - Estimated word count
                - Content summary
                - Scenes covered
                - Key points

            Page numbering must be sequential across the whole novel, chapter references must match the chapter numbers from phase 5, and every scene covered must match a scene number from phase 3.

            Scope limits:

                - Do not write prose, and do not write the final novel text.

            PHASE 7 - CHAPTER SUMMARIES
            ------------------------------------------

            Consumes: the chapter plan from phase 5, the scene plans from phase 3, the story structure from phase 1, the twists and foreshadowing from phase 2, the foundation, and the characters from the input package.

            Responsibility: generate one writing blueprint for every chapter of the chapter plan from phase 5, in chapter number order, with exactly one entry per chapter and no chapter omitted.

            Each entry must define the chapter number, the chapter title, and the chapter summary.

            Each chapter summary must capture:

                - Chapter purpose: why this chapter exists and what it must accomplish.
                - Chapter goals: the goals this chapter must achieve for the story.
                - Characters involved: the characters present and their role in the chapter.
                - Important events: the key events that occur in this chapter.
                - Relevant conflicts: the conflicts that develop or resolve in this chapter.
                - Progression: how the chapter advances the overall story and the character arcs.
                - Important revelations: discoveries, reveals, or twists that occur.
                - Emotional and narrative movement: the emotional arc of the chapter.
                - Scene progression: the ordered scenes and how they build the chapter.
                - Continuity requirements: elements that must remain consistent with the rest of the novel.
                - Chapter ending and setup: how the chapter ends and what it sets up for the next chapter.

            Position each chapter correctly within the overall story progression, follow the scene plans of that chapter exactly, reflect the relevant twists and foreshadowing where appropriate, and preserve the intended chapter structure of phase 5.

            Scope limits:

                - Do not write the prose of any chapter.
                - Do not write the full novel.

            ==================================================
            DEPENDENCY RULES
            ==================================================

            The following dependencies are mandatory. They are the reason the phases must be executed in order.

                Phase 1 must fit the foundation, the characters, the world, and the chronology of the input package.
                Phase 2 must fit the structure of phase 1, the characters, and the world.
                Phase 3 must follow the structure of phase 1, must plant the hidden elements of phase 2, and must use the locations of the input package.
                Phase 4 must support scenes from phase 3 and must use only the characters of the input package.
                Phase 5 must group the scenes of phase 3 into chapters and must follow the structure of phase 1.
                Phase 6 must break the chapters of phase 5 into pages and must map only the scenes of phase 3.
                Phase 7 must write exactly one summary per chapter of phase 5, and each summary must follow the scenes of phase 3 that belong to that chapter.

            The same story elements must never appear under two different names. A character, a location, a faction, a creature, a system, a scene, or a chapter referenced in a later phase must be referenced by the exact identifier that the earlier phase used.

            ==================================================
            FINAL OUTPUT REQUIREMENTS
            ==================================================

            Return one single JSON object that contains the result of all seven phases.

            Do not split the phases across separate responses.

            Do not return the phases one at a time.

            Do not merge two phases into one field, and do not rename a field.

            ==================================================
            JSON STRUCTURE
            ==================================================

            Return ONLY valid JSON.

            {
                \"story_structure\": {
                    \"overall_story_structure\": \"\",
                    \"structure_type\": \"\",
                    \"act_breakdown\": [
                        {
                            \"act\": \"\",
                            \"act_title\": \"\",
                            \"act_purpose\": \"\",
                            \"key_developments\": []
                        }
                    ],
                    \"story_arcs\": [
                        {
                            \"arc_name\": \"\",
                            \"arc_type\": \"\",
                            \"arc_development\": \"\",
                            \"arc_resolution\": \"\"
                        }
                    ],
                    \"main_plot_progression\": [
                        {
                            \"stage\": \"\",
                            \"progression\": \"\",
                            \"function_in_story\": \"\"
                        }
                    ],
                    \"key_story_points\": [
                        {
                            \"story_point\": \"\",
                            \"story_point_type\": \"\",
                            \"where_it_occurs\": \"\",
                            \"narrative_impact\": \"\"
                        }
                    ],
                    \"rising_action\": [],
                    \"climax\": {
                        \"climax_point\": \"\",
                        \"climax_event\": \"\",
                        \"stakes_at_climax\": \"\",
                        \"main_characters_involved\": [],
                        \"climax_impact\": \"\"
                    },
                    \"resolution\": \"\",
                    \"resolution_direction\": \"\",
                    \"high_level_chapter_outline\": [
                        {
                            \"chapter\": \"\",
                            \"chapter_title\": \"\",
                            \"chapter_purpose\": \"\",
                            \"key_events\": [],
                            \"story_structure_placement\": \"\"
                        }
                    ]
                },
                \"twists_and_foreshadowing\": {
                    \"major_twists\": [
                        {
                            \"twist_name\": \"\",
                            \"twist_description\": \"\",
                            \"twist_type\": \"\",
                            \"affected_characters\": [],
                            \"reveal_timing\": \"\",
                            \"story_impact\": \"\"
                        }
                    ],
                    \"clues\": [
                        {
                            \"clue\": \"\",
                            \"clue_type\": \"\",
                            \"planted_where\": \"\",
                            \"planted_when\": \"\",
                            \"connects_to\": \"\",
                            \"how_hidden\": \"\"
                        }
                    ],
                    \"hidden_information\": [
                        {
                            \"hidden_information\": \"\",
                            \"who_knows\": \"\",
                            \"who_does_not_know\": \"\",
                            \"why_hidden\": \"\",
                            \"when_relevant\": \"\"
                        }
                    ],
                    \"secrets\": [],
                    \"mysteries\": [
                        {
                            \"mystery\": \"\",
                            \"mystery_established\": \"\",
                            \"unresolved_until\": \"\",
                            \"resolution_connection\": \"\"
                        }
                    ],
                    \"foreshadowing_elements\": [
                        {
                            \"foreshadowed_element\": \"\",
                            \"planted_where\": \"\",
                            \"how_presented\": \"\",
                            \"prepares\": \"\",
                            \"narrative_effect\": \"\"
                        }
                    ],
                    \"misdirection_elements\": [
                        {
                            \"false_impression\": \"\",
                            \"how_presented\": \"\",
                            \"reader_belief\": \"\",
                            \"true_reveal\": \"\"
                        }
                    ],
                    \"reveal_timing\": [
                        {
                            \"revealed_connection\": \"\",
                            \"reveal_time\": \"\",
                            \"reveal_context\": \"\",
                            \"reader_effect\": \"\"
                        }
                    ],
                    \"connection_with_story\": []
                },
                \"scene_plans\": [
                    {
                        \"scene_number\": \"\",
                        \"scene_title\": \"\",
                        \"story_position\": \"\",
                        \"chapter\": \"\",
                        \"scene_objectives\": [],
                        \"key_events\": [],
                        \"involved_characters\": [],
                        \"location\": \"\",
                        \"time_period\": \"\",
                        \"scene_purpose\": \"\",
                        \"emotional_direction\": \"\"
                    }
                ],
                \"dialogue_plans\": [
                    {
                        \"scene_reference\": \"\",
                        \"involved_characters\": [],
                        \"conversation_context\": \"\",
                        \"dialogue_purpose\": \"\",
                        \"emotional_direction\": \"\",
                        \"character_voice_and_tone\": {},
                        \"emotional_beats\": [],
                        \"dialogue\": [
                            {
                                \"character\": \"\",
                                \"line\": \"\"
                            }
                        ],
                        \"relationship_development\": \"\"
                    }
                ],
                \"chapter_plan\": [
                    {
                        \"chapter_number\": 1,
                        \"title\": \"\",
                        \"summary\": \"\",
                        \"scenes\": [],
                        \"chapter_goals\": [],
                        \"pacing_and_flow\": \"\"
                    }
                ],
                \"page_plan\": [
                    {
                        \"page_number\": 1,
                        \"chapter_reference\": 1,
                        \"estimated_word_count\": 1,
                        \"content_summary\": \"\",
                        \"scenes_covered\": [],
                        \"key_points\": []
                    }
                ],
                \"chapter_summaries\": [
                    {
                        \"chapter_number\": 1,
                        \"chapter_title\": \"\",
                        \"chapter_summary\": {
                            \"chapter_purpose\": \"\",
                            \"chapter_goals\": [],
                            \"characters_involved\": [],
                            \"important_events\": [],
                            \"relevant_conflicts\": [],
                            \"progression\": \"\",
                            \"important_revelations\": [],
                            \"emotional_and_narrative_movement\": \"\",
                            \"scene_progression\": [],
                            \"continuity_requirements\": [],
                            \"chapter_ending_and_setup\": \"\"
                        }
                    }
                ]
            }

            ==================================================
            VALIDATION REQUIREMENTS
            ==================================================

            Before returning the result, verify:

                - All seven phases are present in the response.
                - The story structure follows the input package, and the climax and resolution match the foundation's climax and resolution directions.
                - The chapter outline is high level and consistent with the chronology.
                - Every twist, clue, secret, mystery, foreshadowing element, and misdirection fits the structure and the world.
                - Reveal timings are consistent with the chapter outline.
                - Scene numbering is sequential, every scene belongs to a chapter of the chapter outline, and every scene uses an existing location.
                - Every clue and reveal from phase 2 is planted in a scene.
                - Every dialogue plan references an existing scene and uses existing characters only.
                - Chapter numbers start at 1 and increase by exactly 1, and every scene from phase 3 belongs to exactly one chapter.
                - Page numbers are sequential and page chapter references match the chapter plan.
                - There is exactly one chapter summary per chapter of the chapter plan, in chapter number order.
                - Every chapter summary follows the scene plans of its own chapter and the story structure.
                - No prose chapter and no final novel text are created.
                - The requested language is respected in every value.
                - The response is valid JSON only, with no explanation, markdown, or additional text outside the JSON.
        " . self::jsonOutputContract();

        return $prompt;
    }

    public static function chapterContentPrompt(): string
    {
        $prompt = "
            You are a professional long-form novelist and story writer.

            Your task is to write the complete narrative prose content of a single novel chapter.

            Write only the target chapter.

            Do not write other chapters.

            Do not write the entire novel.

            Do not summarize the chapter.

            Do not generate another chapter plan.

            ==================================================
            PRIMARY RESPONSIBILITY
            ==================================================

            Generate the complete prose content of one chapter, expanding its chapter summary into polished narrative prose while remaining faithful to the established story.

            ==================================================
            LANGUAGE
            ==================================================

            Write the chapter in:

            {{language}}

            Maintain natural vocabulary, grammar, tone, cultural expression, and writing style appropriate for the novel's language.

            ==================================================
            NOVEL FOUNDATION
            ==================================================

            {{foundation}}

            Carefully follow the established novel foundation.

            Understand:

                - Story premise and concept
                - Narrative direction
                - Genre identity and tone
                - Central conflict and stakes
                - Themes and emotional direction
                - Setting direction

            Do not rewrite or contradict the foundation.

            ==================================================
            MAIN CHARACTERS
            ==================================================

            {{characters}}

            Maintain character consistency.

            Understand:

                - Character names, roles, and personalities
                - Motivations, goals, and conflicts
                - Relationships and character arcs
                - Character voice

            Write dialogue and actions that match each character's established voice, personality, and story role.

            Do not change established characters without a reason.

            ==================================================
            WORLD BIBLE
            ==================================================

            {{world_bible}}

            Maintain the established world and its rules.

            Understand:

                - World overview and atmosphere
                - History and lore
                - Cultures and societies
                - Rules and systems
                - Key world elements

            Keep the chapter consistent with the established world.

            Do not invent contradictory world rules.

            ==================================================
            STORY STRUCTURE
            ==================================================

            {{story_structure}}

            Position the target chapter correctly within the overall story progression.

            Respect established chronology and the intended story direction.

            ==================================================
            TWISTS AND FORESHADOWING
            ==================================================

            {{twists_and_foreshadowing}}

            Reflect the relevant twists, foreshadowing, and clues for the target chapter where appropriate.

            ==================================================
            TARGET CHAPTER SUMMARY
            ==================================================

            {{chapter_summary}}

            This is the authoritative writing blueprint for the target chapter.

            This summary was generated specifically for this chapter and must be expanded into polished narrative prose.

            Treat the chapter summary as the primary chapter-specific blueprint for:

                - Chapter purpose
                - Chapter goals
                - Characters involved
                - Important events
                - Relevant conflicts
                - Progression
                - Important revelations
                - Emotional and narrative movement
                - Scene progression
                - Continuity requirements
                - Chapter ending and setup

            Expand the summary into the actual chapter without reinterpreting it into a different story.

            ==================================================
            TARGET CHAPTER PLAN ENTRY
            ==================================================

            {{chapter_plan_entry}}

            This is the plan for the target chapter.

            Understand:

                - Chapter number
                - Chapter title
                - Chapter summary
                - Scenes inside the chapter
                - Chapter goals
                - Pacing and flow

            Preserve the intended chapter structure, pacing, and goals.

            ==================================================
            CHAPTER SCENE PLANS
            ==================================================

            {{scene_plans}}

            These are the scene plans for the scenes covered by the target chapter.

            Understand:

                - Scene objectives
                - Key events per scene
                - Involved characters per scene
                - Locations
                - Time periods
                - Scene purpose
                - Emotional direction

            Develop each planned scene into narrative prose in the established order.

            ==================================================
            CHAPTER DIALOGUE PLANS
            ==================================================

            {{dialogue_plans}}

            These are the planned dialogues for the scenes covered by the target chapter.

            Use them to support the chapter's dialogue.

            Match the planned character voice and tone.

            ==================================================
            WRITING REQUIREMENTS
            ==================================================

            Write a complete, natural, and professionally developed novel chapter.

            The chapter must:

                - Follow the established novel foundation.
                - Follow the established world.
                - Maintain character consistency and character voice.
                - Follow the chapter summary.
                - Follow the chapter plan.
                - Respect established chronology and relationships.
                - Maintain continuity with the rest of the novel.
                - Preserve the intended story direction.
                - Develop the chapter's planned scenes and events.
                - Include appropriate dialogue.
                - Maintain the established narrative style and tone.
                - Advance the story according to the chapter's intended progression.
                - End the chapter according to the chapter's intended progression and setup.

            Write natural, polished narrative prose appropriate for the novel's genre, audience, and language.

            ==================================================
            PROHIBITIONS
            ==================================================

            You must NOT:

                - Rewrite the novel foundation.
                - Change established characters without reason.
                - Invent contradictory world rules.
                - Ignore the chapter summary.
                - Write another chapter.
                - Generate a summary instead of prose.
                - Output analysis of your own writing.
                - Output instructions to a developer.
                - Wrap the content in code fences.

            ==================================================
            OUTPUT FORMAT
            ==================================================

            Return ONLY valid JSON.

            The response must be a single JSON object with exactly one key, chapter_content, holding the full prose of the chapter as one string.

            {
                \"chapter_content\": \"\"
            }

            The prose must not repeat the chapter title as a heading, and must not contain an introduction or a conclusion outside the prose.

            ==================================================
            FINAL CHECK
            ==================================================

            Before returning the result, verify:

                - chapter_content holds the prose of the chapter, not a summary.
                - The prose writes only the target chapter.
                - The prose follows the chapter summary.
                - The prose follows the chapter plan.
                - The prose is consistent with the novel foundation.
                - The prose is consistent with the established characters.
                - The prose is consistent with the established world.
                - The prose maintains continuity with the rest of the novel.
                - The prose includes appropriate dialogue.
                - The prose ends according to the chapter's intended progression.
                - Every line break inside chapter_content is escaped as \\n.
                - The requested language is respected.
                - The response is valid JSON only, with no explanation, markdown, or additional text outside the JSON.
        " . self::jsonOutputContract();

        return $prompt;
    }

    public static function jsonOutputContract(): string
    {
        return "
            ==================================================
            OUTPUT CONTRACT
            ==================================================

            These format rules are mandatory. A response that is not parseable JSON is rejected.

            Return only the raw JSON object described above, starting with the first opening brace and ending with the final closing brace.

            You must NOT:

                - Wrap the JSON in a Markdown code fence.
                - Use \`\`\`json or any other fence marker.
                - Add any explanation, commentary, summary, or reasoning.
                - Add text before the JSON, such as \"Here is the JSON:\".
                - Add text after the JSON, such as \"Let me know if you need changes\".
                - Use Markdown headings, bullet points, or tables.
                - Add comments inside the JSON.
                - Add trailing commas after the last value in an object or an array.
                - Use single quotes for JSON keys or string values.
                - Leave any key or value unquoted.
                - Return an empty string, null, or an empty array purely to satisfy the structure above. Leave out anything the story does not need.

            You must:

                - Use double quotes for every JSON key and every string value.
                - Use the key names and nesting shown above for the content you do include.
                - Match the value type shown above: a quoted value is a string, a bare number is a number, and [ ] is an array.
                - Populate every array you include with real content.
                - Escape every double quote inside a string value as \\\".
                - Escape every backslash as \\\\.
                - Escape every literal newline inside a string value as \\n and every literal tab as \\t. Do not place a raw line break inside a string value.
                - Keep the JSON syntactically complete even when the generated prose contains quotation marks, apostrophes, colons, commas, brackets, braces, slashes, or non-Latin characters.

            The structure above is a guide to the depth of a complete package, not a fixed template. Omit any listed field that has no real content for this particular story, and add any field this story genuinely needs. Do not invent placeholder or filler content to fill a field.

            The entire response must be parseable by a standard JSON parser in a single pass.

            Return the JSON now, and nothing else.
        ";
    }

    public static function maxOutputTokenInstruction(?int $maxOutputTokens): string
    {
        if ($maxOutputTokens === null || $maxOutputTokens < 1) {
            return '';
        }

        return "
            ==================================================
            OUTPUT TOKEN BUDGET
            ==================================================

            Maximum output tokens: {$maxOutputTokens}.

            The complete response must fit inside this budget. Never exceed it, and never let the JSON be cut off before the final closing brace.

            Write dense and specific values. Prefer short, concrete sentences over long, repetitive ones. Never pad a field with restated information that another field already carries.

            If you are running out of budget, shorten the wording of the values and drop fields that carry the least value. Never rename a field, and never return truncated text.
        ";
    }

    public static function applyMaxOutputTokenInstruction(string $prompt, ?int $maxOutputTokens): string
    {
        $instruction = self::maxOutputTokenInstruction($maxOutputTokens);

        if ($instruction === '') {
            return $prompt;
        }

        return rtrim($prompt)."\n".$instruction;
    }

    public static function generateFullPrompt(string $partialPrompt, array $receivedInputs): string
    {
        $search  = [];
        $replace = [];

        foreach ($receivedInputs as $key => $value) {
            $search[]  = '{{' . $key . '}}';
            $replace[] = $value ?? '';
        }

        return str_replace($search, $replace, $partialPrompt);
    }

    public static function unresolvedPlaceholders(string $fullPrompt): array
    {
        preg_match_all('/\{\{\s*([A-Za-z0-9_]+)\s*\}\}/', $fullPrompt, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }
}
