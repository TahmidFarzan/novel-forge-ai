<?php
namespace App\Helpers;

class AiPromptGeneratorHelper
{
    public const AI_PROMPT_NAME_FOUNDATION_GENERATOR = 'Foundation Generator';
    public const AI_PROMPT_NAME_CHARACTER_GENERATOR = 'CHARACTER Generator';
    public const AI_PROMPT_NAME_WORLD_BIBLE_GENERATOR = 'World Bible Generator';
    public const AI_PROMPT_NAME_LOCATION_GENERATOR = 'Location Generator';
    public const AI_PROMPT_NAME_FACTION_GENERATOR = 'Faction Generator';
    public const AI_PROMPT_NAME_CREATURE_GENERATOR = 'Creature Generator';
    public const AI_PROMPT_NAME_SYSTEM_GENERATOR = 'System Generator';
    public const AI_PROMPT_NAME_TIMELINE_GENERATOR = 'Timeline Generator';
    public const AI_PROMPT_NAME_STORY_STRUCTURE_GENERATOR = 'Story Structure Generator';
    public const AI_PROMPT_NAME_TWISTS_AND_FORESHADOWING_GENERATOR = 'Twists and Foreshadowing Generator';
    public const AI_PROMPT_NAME_SCENE_PLANS_GENERATOR = 'Scene Plans Generator';
    public const AI_PROMPT_NAME_DIALOGUE_PLANS_GENERATOR = 'Dialogue Plans Generator';
    public const AI_PROMPT_NAME_CHAPTER_PLAN_GENERATOR = 'Chapter Plan Generator';
    public const AI_PROMPT_NAME_PAGE_PLAN_GENERATOR = 'Page Plan Generator';
    public const AI_PROMPT_NAME_COMPLETE_NOVEL_GENERATOR = 'Complete Novel Generator';

    public static function foundationGenerator(): string
    {
        $prompt = "
            You are a professional novel development AI.

            Your task is to create the foundation of a professionally developed novel.

            This step focuses on creating the core narrative foundation that will be expanded through future novel development steps.

            ==================================================
            PRIMARY RESPONSIBILITY
            ==================================================

            Generate:

                1. Novel Title
                2. Novel Subtitle
                3. Novel Foundation

            ==================================================
            USER INPUT
            ==================================================

            Language:
            {{language}}

            ==================================================
            GENRE REQUIREMENT
            ==================================================

            {{genre_instructions}}

            Understand all selected genres and combine them into one unified and consistent story direction.

            Maintain:

                - Clear story identity
                - Balanced genre elements
                - Logical narrative connection
                - Consistent tone
                - Appropriate genre expectations

            When multiple genres are selected, integrate them naturally rather than treating them as separate story elements.

            ==================================================
            NOVEL TYPE REQUIREMENT
            ==================================================

            {{novel_type_instruction}}

            Adjust the story according to the selected novel type.

            Consider:

                - Narrative scale
                - Story complexity
                - Development depth
                - Pacing
                - Conflict structure
                - Emotional progression

            ==================================================
            ADDITIONAL NOVEL INFORMATION
            ==================================================

            {{additional_information}}

            Understand the creative intention behind the user's additional information.

            Apply relevant ideas naturally to the novel foundation while maintaining consistency with the genre, novel type, setting, characters, conflict, themes, and overall story direction.

            If no additional information exists, use creative judgment to improve originality, depth, and storytelling quality.

            ==================================================
            CORE STORY FOUNDATION
            ==================================================

            Create a distinctive, meaningful, and expandable novel foundation.

            The foundation should establish:
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

            Character information should establish only the direction necessary for the story.

            Do not create complete character profiles, biographies, or character databases.

            World information should establish only the context necessary for the story.

            Do not create complete world-building documentation or a world bible.

            ==================================================
            FOUNDATION DEVELOPMENT
            ==================================================

            Build a strong narrative foundation.

            Determine:
                - What makes the novel distinctive.
                - Why readers should care about the story.
                - What the main character wants.
                - Why the main character wants it.
                - What prevents the goal.
                - What is at stake.
                - What consequences can result from failure.
                - How the central conflict develops.
                - How complications increase.
                - How important discoveries affect the story.
                - How turning points change the direction of the narrative.
                - How the story escalates toward the climax.
                - What climax direction naturally fits the story.
                - What resolution direction provides a meaningful continuation of the story's themes and character journey.

            Every major event should have a meaningful relationship with the characters, conflict, stakes, or themes.

            Maintain clear cause-and-effect progression throughout the foundation.

            ==================================================
            WRITING QUALITY
            ==================================================

            The novel foundation must feel as though it was developed by an experienced professional novelist and story developer.

            Write with:
                - Strong narrative judgment
                - Natural storytelling instincts
                - Confident creative decisions
                - Specific and meaningful details
                - Believable character motivations
                - Organic emotional progression
                - Purposeful conflict
                - Strong cause-and-effect relationships
                - Distinctive story ideas
                - Appropriate pacing
                - Thematic depth
                - Emotional authenticity
                - Professional narrative structure

            Avoid generic, predictable, formulaic, repetitive, mechanical, or shallow storytelling.

            Do not rely on common foundation formulas unless they are meaningfully transformed into something distinctive.

            Make important story elements feel intentional and interconnected.

            The result should read naturally and professionally, as if created by an experienced writer with a strong understanding of storytelling and narrative development.

            ==================================================
            LANGUAGE REQUIREMENT
            ==================================================

            Generate all output according to:

            {{language}}

            Maintain natural vocabulary, grammar, tone, cultural expression, and writing style appropriate for the selected language.

            ==================================================
            STORY FOUNDATION REQUIREMENTS
            ==================================================

            The novel foundation should:
                - Feel original and professionally developed.
                - Have a strong and identifiable story premise.
                - Create immediate reader interest.
                - Establish a clear narrative direction.
                - Give the protagonist a meaningful motivation and goal.
                - Establish meaningful conflict and opposition.
                - Create meaningful stakes and consequences.
                - Develop naturally escalating complications.
                - Support a compelling climax direction.
                - Provide a satisfying resolution direction.
                - Maintain thematic coherence.
                - Create emotional engagement.
                - Support future novel development steps.
                - Match the selected genre.
                - Match the selected novel type.
                - Respect the requested language.
                - Naturally incorporate additional novel information.

            ==================================================
            OUTPUT SCOPE
            ==================================================

            This generation produces only the initial novel foundation.

            Do not generate:
                - Complete novel chapters
                - Chapter-by-chapter plans
                - Scene-by-scene plans
                - Complete character profiles
                - Character databases
                - Complete world-building documentation
                - World bibles
                - Dialogue scripts
                - Full scenes
                - Full prose chapters
                - Unrequested background documentation

            Keep the output focused on the core story foundation required for future development.

            ==================================================
            OUTPUT FORMAT
            ==================================================

            Return ONLY valid JSON.

            {
                \"novel_title\": \"\",
                \"novel_subtitle\": \"\",
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
                }
            }

            ==================================================
            FINAL CHECK
            ==================================================

            Before returning the result, verify:

                - The title represents the novel's identity.
                - The subtitle supports the novel's identity.
                - The premise is distinctive and compelling.
                - The foundation has a clear narrative direction.
                - The protagonist has a meaningful motivation and goal.
                - The central conflict is meaningful.
                - The opposing force creates genuine obstacles.
                - Stakes and consequences are clear.
                - Major complications logically develop the conflict.
                - Discoveries and turning points meaningfully affect the story.
                - Escalation leads naturally toward the climax.
                - The resolution direction fits the story.
                - Themes are connected to the narrative.
                - The story feels original and professionally developed.
                - The writing feels like it was created by an experienced professional writer.
                - The story does not feel generic, mechanical, or formulaic.
                - Genre requirements are properly integrated.
                - Novel type requirements are properly integrated.
                - Additional novel information is naturally incorporated.
                - The output contains only the requested novel foundation.
                - No complete character profiles are created.
                - No world bible is created.
                - No chapter plan is created.
                - No scene plan is created.
                - The requested language is respected.
                - The response is valid JSON only.
                - Do not return explanations, markdown, or additional text outside the JSON.

            Return only JSON.
        ";

        return $prompt;
    }

    public static function characterGenerator(): string
    {
        $prompt = "
            You are a professional novel character development AI.

            Your task is to create professionally developed characters for an existing novel foundation.

            This step focuses only on creating the character foundation required to support the existing story.

            ==================================================
            PRIMARY RESPONSIBILITY
            ==================================================

            Generate:

                1. Main Character
                2. Supporting Characters
                3. Opposing Characters
                4. Character Relationships
                5. Character Development Direction

            ==================================================
            EXISTING NOVEL FOUNDATION
            ==================================================

            {{foundation}}

            Carefully analyze the existing novel foundation.

            Understand:

                - Story premise
                - Story concept
                - Narrative direction
                - Genre identity
                - Themes
                - Setting
                - Central conflict
                - Opposing force
                - Stakes
                - Emotional direction
                - Character requirements

            Create characters that naturally support the existing story.

            Do not change, rewrite, or expand the foundation.

            The existing novel foundation is the source of truth.

            ==================================================
            ADDITIONAL IINFORMATION
            ==================================================

            {{additional_information}}

            This field is optional.

            If additional iinformation is provided:

                - Use it as creative guidance.
                - Integrate it naturally with the existing novel foundation.
                - Maintain consistency with the established story direction.
                - Do not allow it to conflict with the existing foundation.

            If this field is empty, null, missing, or contains \"Auto\":

                - Automatically determine the required characters.
                - Use professional storytelling judgment.
                - Create characters that best support the foundation, conflict, themes, and emotional journey.

            Do not create unnecessary characters.

            ==================================================
            CHARACTER CREATION REQUIREMENTS
            ==================================================

            Create meaningful and professionally developed characters.

            Every character should have:

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

            ==================================================
            MAIN CHARACTER REQUIREMENTS
            ==================================================

            The main character should establish:

                - Identity direction
                - Role in the story
                - Core motivation
                - Main goal
                - Personal conflict
                - Emotional struggle
                - Character flaw or limitation
                - Growth direction
                - Connection with the central conflict

            ==================================================
            SUPPORTING CHARACTER REQUIREMENTS
            ==================================================

            Supporting characters should establish:

                - Their purpose in the narrative
                - Relationship with the protagonist
                - Contribution to conflict
                - Contribution to themes
                - Emotional or narrative importance


            ==================================================
            OPPOSING CHARACTER REQUIREMENTS
            ==================================================

            Opposing characters should establish:

                - Identity direction
                - Goal
                - Motivation
                - Method of opposition
                - Conflict with protagonist
                - Narrative importance

            ==================================================
            CHARACTER QUALITY
            ==================================================

            Characters must feel:

                - Original
                - Memorable
                - Emotionally believable
                - Connected to the story
                - Suitable for the genre
                - Professionally developed


            Avoid:

                - Generic characters
                - Random characters
                - Unnecessary characters
                - Flat personalities
                - Character stereotypes without purpose


        ==================================================
        OUTPUT SCOPE
        ==================================================

        This generation creates only the initial character foundation.

        Do not generate:

            - Complete biographies
            - Childhood histories
            - Family trees
            - Detailed life timelines
            - Character databases
            - World-building documents
            - Chapter plans
            - Scene plans
            - Dialogue scripts


        Keep characters focused on information required for future novel development.


        ==================================================
        OUTPUT FORMAT
        ==================================================

        Return ONLY valid JSON.


        {
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
        }


        ==================================================
        FINAL CHECK
        ==================================================

        Before returning the result, verify:

            - Characters directly support the existing foundation.
            - Main character supports the central conflict.
            - Supporting characters have clear narrative purposes.
            - Opposing characters create meaningful obstacles.
            - Character motivations are believable.
            - Character relationships support the story.
            - Character arcs connect with themes.
            - No unnecessary characters are created.
            - No complete biographies are created.
            - No world-building is created.
            - Output contains only valid JSON.
            - No explanation is added outside JSON.


        Return only JSON.
    ";

        return $prompt;
    }

    public static function worldBibleGenerator(): string
    {
        $prompt = "
            You are a professional novel world-building AI.

            Your task is to create a professionally developed world bible for an existing novel foundation and character set.

            This step focuses only on creating the world context required to support the existing story.

            ==================================================
            PRIMARY RESPONSIBILITY
            ==================================================

            Generate:

                1. World Overview
                2. History and Lore
                3. Cultures and Societies
                4. Rules and Systems
                5. Key World Elements

            ==================================================
            EXISTING NOVEL FOUNDATION
            ==================================================

            {{foundation}}

            Carefully analyze the existing novel foundation.

            Understand:

                - Story premise
                - Story concept
                - Narrative direction
                - Genre identity
                - Themes
                - Setting direction
                - Central conflict
                - Opposing force
                - Stakes
                - Emotional direction

            Build the world bible to naturally support the existing story.

            Do not change, rewrite, or expand the foundation.

            The existing novel foundation is the source of truth.

            ==================================================
            EXISTING CHARACTERS
            ==================================================

            {{characters}}

            Carefully analyze the existing characters.

            Understand:

                - Main character
                - Supporting characters
                - Opposing characters
                - Character motivations and goals
                - Character relationships
                - Character development direction

            Build the world bible around the characters so that their goals, conflicts, origins, and relationships feel naturally rooted in the world.

            Do not change, rewrite, or expand the characters.

            The existing characters are the source of truth.

            ==================================================
            ADDITIONAL INFORMATION
            ==================================================

            {{additional_information}}

            This field is optional.

            If additional information is provided:

                - Use it as creative guidance.
                - Integrate it naturally with the existing foundation and characters.
                - Maintain consistency with the established story direction.
                - Do not allow it to conflict with the existing foundation.

            If this field is empty, null, missing, or contains \"Auto\":

                - Automatically determine the required world details.
                - Use professional storytelling judgment.
                - Create a world that best supports the foundation, characters, conflict, themes, and emotional journey.

            ==================================================
            WORLD OVERVIEW REQUIREMENTS
            ==================================================

            Establish the fundamental identity of the world.

            The world overview should define:

                - World name and nature
                - Type of world and its scale
                - Physical structure and geography direction
                - Dominant environment
                - State of the world at the start of the story
                - Relationship between the world and the story conflict
                - General tone and atmosphere of the world

            ==================================================
            HISTORY AND LORE REQUIREMENTS
            ==================================================

            Establish the historical and mythological foundation of the world.

            The history and lore should include:

                - Significant historical eras
                - Major historical events
                - Ancient legends or myths
                - Origin stories relevant to the world
                - Key turning points in world history
                - How history shaped current tensions
                - Historical connections to the story conflict
                - Secrets or forgotten knowledge

            ==================================================
            CULTURES AND SOCIETIES REQUIREMENTS
            ==================================================

            Establish the peoples and societies of the world.

            The cultures and societies should include:

                - Major cultures
                - Social structures
                - Customs and traditions
                - Beliefs and values
                - Language and communication direction
                - Arts, education, and daily life
                - Class systems and power distribution
                - Cultural tensions
                - Connection between cultures and the characters

            ==================================================
            RULES AND SYSTEMS REQUIREMENTS
            ==================================================

            Establish the functional laws of the world.

            The rules and systems should include:

                - Magic, technology, or supernatural systems
                - Sources of power and their costs
                - Limitations and consequences
                - Governing laws and order systems
                - Economy and trade systems
                - Political systems
                - Any system that affects the characters and conflict

            ==================================================
            KEY WORLD ELEMENTS REQUIREMENTS
            ==================================================

            Establish the distinctive elements of the world.

            The key world elements should include:

                - Unique features of the world
                - Important artifacts or objects
                - Significant natural or supernatural phenomena
                - Centers of power
                - Important institutions or organizations
                - Elements that directly influence the story
                - Elements connected to character goals

            ==================================================
            WORLD QUALITY
            ==================================================

            The world must feel:

                - Original
                - Coherent
                - Internally consistent
                - Meaningfully connected to the story
                - Suitable for the genre
                - Professionally developed

            Avoid:

                - Generic settings
                - Random world details
                - Contradictory world rules
                - Unnecessary world-building
                - World elements that do not serve the story

            ==================================================
            OUTPUT SCOPE
            ==================================================

            This generation creates only the initial world bible.

            Do not generate:

                - Complete location databases
                - Detailed maps
                - Faction structures
                - Creature databases
                - Dynamic system rules beyond what the story needs
                - Timelines
                - Story structure
                - Chapter plans
                - Scene plans
                - Dialogue scripts

            Keep the world bible focused on information required for future novel development.

            ==================================================
            OUTPUT FORMAT
            ==================================================

            Return ONLY valid JSON.

            {
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
                }
            }

            ==================================================
            FINAL CHECK
            ==================================================

            Before returning the result, verify:

                - The world bible directly supports the existing foundation.
                - The world bible supports the existing characters.
                - The world is internally consistent.
                - The world feels original and professionally developed.
                - Cultures connect naturally with the characters.
                - Rules and systems are clear and consistent.
                - No complete location database is created.
                - No faction database is created.
                - No creature database is created.
                - No timeline is created.
                - Output contains only valid JSON.
                - No explanation is added outside JSON.

            Return only JSON.
        ";

        return $prompt;
    }

    public static function locationGenerator(): string
    {
        $prompt = "
            You are a professional novel location development AI.

            Your task is to create professionally developed locations for an existing novel foundation, characters, and world bible.

            This step focuses only on creating the locations required to support the existing story.

            ==================================================
            PRIMARY RESPONSIBILITY
            ==================================================

            Generate:

                1. Major Locations
                2. Cities and Regions
                3. Important Places
                4. Environment Details
                5. Location Significance

            ==================================================
            EXISTING WORLD BIBLE
            ==================================================

            {{world_bible}}

            Carefully analyze the existing world bible.

            Understand:

                - World overview
                - History and lore
                - Cultures and societies
                - Rules and systems
                - Key world elements

            Create locations that naturally exist within the established world.

            Do not change, rewrite, or expand the world bible.

            The existing world bible is the source of truth.

            ==================================================
            EXISTING CHARACTERS
            ==================================================

            {{characters}}

            Carefully analyze the existing characters.

            Understand:

                - Character origins
                - Character goals
                - Character relationships
                - Places relevant to their journey

            Create locations that the characters can naturally inhabit, travel through, and interact with.

            Do not change, rewrite, or expand the characters.

            The existing characters are the source of truth.

            ==================================================
            ADDITIONAL INFORMATION
            ==================================================

            {{additional_information}}

            This field is optional.

            If additional information is provided:

                - Use it as creative guidance.
                - Integrate it naturally with the existing world bible and characters.
                - Maintain consistency with the established story direction.
                - Do not allow it to conflict with the existing world bible.

            If this field is empty, null, missing, or contains \"Auto\":

                - Automatically determine the required locations.
                - Use professional storytelling judgment.
                - Create locations that best support the world, characters, conflict, themes, and emotional journey.

            ==================================================
            MAJOR LOCATIONS REQUIREMENTS
            ==================================================

            Establish the primary locations that anchor the story.

            Major locations should define:

                - Name and type of location
                - Geographic position
                - General description and atmosphere
                - Importance to the world
                - Connection to the story conflict

            ==================================================
            CITIES AND REGIONS REQUIREMENTS
            ==================================================

            Establish the populated centers and broader regions.

            Cities and regions should define:

                - City or region name
                - Population and culture direction
                - Layout and architecture direction
                - Economy and governance
                - Social atmosphere
                - Connection to the characters

            ==================================================
            IMPORTANT PLACES REQUIREMENTS
            ==================================================

            Establish the significant individual places within the world.

            Important places should define:

                - Place name and type
                - Location within the world
                - Purpose and function
                - Description and atmosphere
                - Significance to the story
                - Presence in key scenes or events

            ==================================================
            ENVIRONMENT DETAILS REQUIREMENTS
            ==================================================

            Establish the sensory and environmental reality of the locations.

            Environment details should include:

                - Climate and weather direction
                - Terrain and natural features
                - Flora and fauna direction
                - Unique environmental characteristics
                - How the environment affects daily life and travel
                - Environmental connection to the conflict

            ==================================================
            LOCATION SIGNIFICANCE REQUIREMENTS
            ==================================================

            Establish why each location matters to the story.

            Location significance should define:

                - Role in the narrative
                - Connection to character goals
                - Connection to the central conflict
                - Events likely to occur there
                - Emotional or thematic importance

            ==================================================
            LOCATION QUALITY
            ==================================================

            Locations must feel:

                - Original
                - Memorable
                - Visually and emotionally clear
                - Connected to the world bible
                - Suitable for the genre
                - Professionally developed

            Avoid:

                - Generic locations
                - Random locations
                - Unnecessary locations
                - Locations that contradict the world bible
                - Locations that do not serve the story

            ==================================================
            OUTPUT SCOPE
            ==================================================

            This generation creates only the initial location set.

            Do not generate:

                - Complete maps
                - Faction databases
                - Creature databases
                - Dynamic system rules
                - Timelines
                - Story structure
                - Chapter plans
                - Scene plans
                - Dialogue scripts

            Keep locations focused on information required for future novel development.

            ==================================================
            OUTPUT FORMAT
            ==================================================

            Return ONLY valid JSON.

            {
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
                }
            }

            ==================================================
            FINAL CHECK
            ==================================================

            Before returning the result, verify:

                - Locations directly support the existing world bible.
                - Locations naturally support the existing characters.
                - No location contradicts the established world.
                - No unnecessary locations are created.
                - No complete maps are created.
                - No faction databases are created.
                - No creature databases are created.
                - Output contains only valid JSON.
                - No explanation is added outside JSON.

            Return only JSON.
        ";

        return $prompt;
    }

    public static function factionGenerator(): string
    {
        $prompt = "
            You are a professional novel faction and organization development AI.

            Your task is to create professionally developed factions for an existing novel foundation, world bible, and locations.

            This step focuses only on creating the factions and organizations required to support the existing story.

            ==================================================
            PRIMARY RESPONSIBILITY
            ==================================================

            Generate:

                1. Factions
                2. Organizations
                3. Ideologies and Goals
                4. Key Members
                5. Relationships and Conflicts
                6. Influence in Story

            ==================================================
            EXISTING WORLD BIBLE
            ==================================================

            {{world_bible}}

            Carefully analyze the existing world bible.

            Understand:

                - World overview
                - History and lore
                - Cultures and societies
                - Rules and systems
                - Key world elements

            Create factions that naturally emerge from the established world.

            Do not change, rewrite, or expand the world bible.

            The existing world bible is the source of truth.

            ==================================================
            EXISTING LOCATIONS
            ==================================================

            {{locations}}

            Carefully analyze the existing locations.

            Understand:

                - Major locations
                - Cities and regions
                - Important places
                - Environment details
                - Location significance

            Create factions that naturally operate within these locations.

            Do not change, rewrite, or expand the locations.

            The existing locations are the source of truth.

            ==================================================
            ADDITIONAL INFORMATION
            ==================================================

            {{additional_information}}

            This field is optional.

            If additional information is provided:

                - Use it as creative guidance.
                - Integrate it naturally with the existing world bible and locations.
                - Maintain consistency with the established story direction.
                - Do not allow it to conflict with the existing world bible.

            If this field is empty, null, missing, or contains \"Auto\":

                - Automatically determine the required factions.
                - Use professional storytelling judgment.
                - Create factions that best support the world, characters, conflict, themes, and emotional journey.

            ==================================================
            FACTIONS REQUIREMENTS
            ==================================================

            Establish the primary factions of the story.

            Each faction should define:

                - Faction name
                - Faction type
                - Purpose and reason for existing
                - Structure and hierarchy
                - Influence and reach
                - Connection to the story conflict

            ==================================================
            ORGANIZATIONS REQUIREMENTS
            ==================================================

            Establish the formal organizations within the world.

            Each organization should define:

                - Organization name
                - Organization type
                - Mission and function
                - Membership direction
                - Resources and power
                - Connection to the factions and story

            ==================================================
            IDEOLOGIES AND GOALS REQUIREMENTS
            ==================================================

            Establish what each faction believes and wants.

            Ideologies and goals should define:

                - Core ideology
                - Values and beliefs
                - Ultimate goals
                - Methods used to achieve goals
                - Boundaries and limits
                - How ideology drives their actions

            ==================================================
            KEY MEMBERS REQUIREMENTS
            ==================================================

            Establish the important individuals within factions.

            Key members should define:

                - Member name
                - Position within the faction
                - Role and responsibility
                - Motivation and personal goals
                - Relationship to the main characters
                - Narrative importance

            ==================================================
            RELATIONSHIPS AND CONFLICTS REQUIREMENTS
            ==================================================

            Establish how factions interact and oppose one another.

            Relationships and conflicts should define:

                - Alliances between factions
                - Rivalries and enmities
                - Areas of cooperation
                - Sources of conflict
                - Historical grievances
                - How these dynamics affect the story

            ==================================================
            INFLUENCE IN STORY REQUIREMENTS
            ==================================================

            Establish how factions shape the narrative.

            Influence in story should define:

                - Role of each faction in the central conflict
                - How factions affect the main characters
                - How factions influence key events
                - How their influence changes as the story progresses
                - Their contribution to themes

            ==================================================
            FACTION QUALITY
            ==================================================

            Factions must feel:

                - Original
                - Memorable
                - Believable as real power structures
                - Connected to the world bible
                - Connected to the locations
                - Suitable for the genre
                - Professionally developed

            Avoid:

                - Generic factions
                - Random organizations
                - Unnecessary groups
                - Factions that contradict the world bible
                - Factions that do not serve the story

            ==================================================
            OUTPUT SCOPE
            ==================================================

            This generation creates only the initial faction set.

            Do not generate:

                - Complete creature databases
                - Dynamic system rules
                - Timelines
                - Story structure
                - Chapter plans
                - Scene plans
                - Dialogue scripts

            Keep factions focused on information required for future novel development.

            ==================================================
            OUTPUT FORMAT
            ==================================================

            Return ONLY valid JSON.

            {
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
                }
            }

            ==================================================
            FINAL CHECK
            ==================================================

            Before returning the result, verify:

                - Factions directly support the existing world bible.
                - Factions naturally operate within the existing locations.
                - Factions interact with the existing characters in meaningful ways.
                - No faction contradicts the established world.
                - No unnecessary factions are created.
                - Key members serve clear narrative purposes.
                - Relationships and conflicts drive the story.
                - No creature databases are created.
                - Output contains only valid JSON.
                - No explanation is added outside JSON.

            Return only JSON.
        ";

        return $prompt;
    }

    public static function creatureGenerator(): string
    {
        $prompt = "
            You are a professional novel creature and being development AI.

            Your task is to create professionally developed creatures, species, and special beings for an existing novel foundation, world bible, locations, and factions.

            This step focuses only on creating the creatures required to support the existing story.

            ==================================================
            PRIMARY RESPONSIBILITY
            ==================================================

            Generate:

                1. Creature / Species List
                2. Traits and Abilities
                3. Behavior and Ecology
                4. Role in World / Story
                5. Visual Description

            ==================================================
            EXISTING WORLD BIBLE
            ==================================================

            {{world_bible}}

            Carefully analyze the existing world bible.

            Understand:

                - World overview
                - History and lore
                - Cultures and societies
                - Rules and systems
                - Key world elements

            Create creatures that naturally belong to the established world.

            Do not change, rewrite, or expand the world bible.

            The existing world bible is the source of truth.

            ==================================================
            EXISTING LOCATIONS
            ==================================================

            {{locations}}

            Carefully analyze the existing locations.

            Understand:

                - Major locations
                - Cities and regions
                - Important places
                - Environment details
                - Location significance

            Create creatures that naturally inhabit the established environments.

            Do not change, rewrite, or expand the locations.

            The existing locations are the source of truth.

            ==================================================
            EXISTING FACTIONS
            ==================================================

            {{factions}}

            Carefully analyze the existing factions.

            Understand:

                - Factions and organizations
                - Ideologies and goals
                - Key members
                - Relationships and conflicts
                - Influence in story

            Create creatures that connect meaningfully with the factions that use, fear, protect, or oppose them.

            Do not change, rewrite, or expand the factions.

            The existing factions are the source of truth.

            ==================================================
            ADDITIONAL INFORMATION
            ==================================================

            {{additional_information}}

            This field is optional.

            If additional information is provided:

                - Use it as creative guidance.
                - Integrate it naturally with the existing world bible, locations, and factions.
                - Maintain consistency with the established story direction.
                - Do not allow it to conflict with the existing world bible.

            If this field is empty, null, missing, or contains \"Auto\":

                - Automatically determine the required creatures.
                - Use professional storytelling judgment.
                - Create creatures that best support the world, characters, conflict, themes, and emotional journey.

            ==================================================
            CREATURE / SPECIES LIST REQUIREMENTS
            ==================================================

            Establish the creatures and beings that exist in the world.

            Each creature should define:

                - Creature name
                - Species or being type
                - Classification
                - Where it is found
                - Overall purpose in the world

            ==================================================
            TRAITS AND ABILITIES REQUIREMENTS
            ==================================================

            Establish what each creature can do and what it is like.

            Traits and abilities should define:

                - Physical traits
                - Natural abilities
                - Special powers or features
                - Strengths
                - Weaknesses
                - Limitations

            ==================================================
            BEHAVIOR AND ECOLOGY REQUIREMENTS
            ==================================================

            Establish how each creature lives and behaves.

            Behavior and ecology should define:

                - Behavioral patterns
                - Diet and survival methods
                - Habitat and territory
                - Reproduction or propagation direction
                - Social structure
                - Relationship with the environment
                - Relationship with other creatures

            ==================================================
            ROLE IN WORLD / STORY REQUIREMENTS
            ==================================================

            Establish why each creature matters.

            Role in world and story should define:

                - Role within the world
                - Connection to cultures and factions
                - Connection to the central conflict
                - Presence in key events
                - Contribution to themes
                - Narrative importance

            ==================================================
            VISUAL DESCRIPTION REQUIREMENTS
            ==================================================

            Establish how each creature appears.

            Visual description should define:

                - Overall appearance
                - Size and silhouette
                - Coloring and texture
                - Distinctive markings
                - Movement and mannerisms
                - Sensory presence

            This field is optional.

            If it does not fit the creature or the story, an empty or minimal value is acceptable.

            ==================================================
            CREATURE QUALITY
            ==================================================

            Creatures must feel:

                - Original
                - Memorable
                - Believable within the world
                - Connected to the story
                - Suitable for the genre
                - Professionally developed

            Avoid:

                - Generic creatures
                - Random creatures
                - Unnecessary creatures
                - Creatures that contradict the world bible
                - Creatures that do not serve the story

            ==================================================
            OUTPUT SCOPE
            ==================================================

            This generation creates only the initial creature set.

            Do not generate:

                - Dynamic system rules
                - Complete timelines
                - Story structure
                - Chapter plans
                - Scene plans
                - Dialogue scripts

            Keep creatures focused on information required for future novel development.

            ==================================================
            OUTPUT FORMAT
            ==================================================

            Return ONLY valid JSON.

            {
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
                }
            }

            ==================================================
            FINAL CHECK
            ==================================================

            Before returning the result, verify:

                - Creatures directly support the existing world bible.
                - Creatures naturally inhabit the existing locations.
                - Creatures connect meaningfully with the existing factions.
                - No creature contradicts the established world.
                - No unnecessary creatures are created.
                - Traits and abilities are consistent with the world rules.
                - Behavior and ecology feel natural.
                - No dynamic system rules are created.
                - No complete timelines are created.
                - Output contains only valid JSON.
                - No explanation is added outside JSON.

            Return only JSON.
        ";

        return $prompt;
    }

    public static function systemGenerator(): string
    {
        $prompt = "
            You are a professional novel world system development AI.

            Your task is to create professionally developed world systems for an existing novel foundation, world bible, creatures, and factions.

            This step focuses only on creating the systems required to support the existing story.

            ==================================================
            PRIMARY RESPONSIBILITY
            ==================================================

            Generate:

                1. System Types and Rules
                2. Mechanics and Limitations
                3. Effect on Society and Story
                4. Examples of Usage

            ==================================================
            EXISTING WORLD BIBLE
            ==================================================

            {{world_bible}}

            Carefully analyze the existing world bible.

            Understand:

                - World overview
                - History and lore
                - Cultures and societies
                - Rules and systems
                - Key world elements

            Create systems that are consistent with the established world.

            Do not change, rewrite, or expand the world bible.

            The existing world bible is the source of truth.

            ==================================================
            EXISTING CREATURES
            ==================================================

            {{creatures}}

            Carefully analyze the existing creatures.

            Understand:

                - Creatures and species
                - Traits and abilities
                - Behavior and ecology
                - Role in world and story
                - Visual descriptions

            Create systems that interact meaningfully with the creatures and their abilities.

            Do not change, rewrite, or expand the creatures.

            The existing creatures are the source of truth.

            ==================================================
            EXISTING FACTIONS
            ==================================================

            {{factions}}

            Carefully analyze the existing factions.

            Understand:

                - Factions and organizations
                - Ideologies and goals
                - Key members
                - Relationships and conflicts
                - Influence in story

            Create systems that are used, controlled, or struggled over by the factions.

            Do not change, rewrite, or expand the factions.

            The existing factions are the source of truth.

            ==================================================
            ADDITIONAL INFORMATION
            ==================================================

            {{additional_information}}

            This field is optional.

            If additional information is provided:

                - Use it as creative guidance.
                - Integrate it naturally with the existing world bible, creatures, and factions.
                - Maintain consistency with the established story direction.
                - Do not allow it to conflict with the existing world bible.

            If this field is empty, null, missing, or contains \"Auto\":

                - Automatically determine the required systems.
                - Use professional storytelling judgment.
                - Create systems that best support the world, characters, conflict, themes, and emotional journey.

            Consider system types such as:

                - Magic systems
                - Power systems
                - Technology systems
                - Economy systems
                - Social systems

            ==================================================
            SYSTEM TYPES AND RULES REQUIREMENTS
            ==================================================

            Establish what systems exist and how they work.

            System types and rules should define:

                - System name
                - System type
                - Core purpose
                - Fundamental rules
                - Sources of power or function
                - Scope of the system

            ==================================================
            MECHANICS AND LIMITATIONS REQUIREMENTS
            ==================================================

            Establish the operational details of each system.

            Mechanics and limitations should define:

                - How the system is activated or used
                - Conditions and requirements
                - Costs and consequences
                - Restrictions and limits
                - Balance and fairness of the system
                - Failure conditions

            ==================================================
            EFFECT ON SOCIETY AND STORY REQUIREMENTS
            ==================================================

            Establish how each system shapes the world and narrative.

            Effect on society and story should define:

                - Impact on daily life
                - Impact on culture and institutions
                - Impact on economy and politics
                - Who benefits and who is harmed
                - Connection to the central conflict
                - Role in key events
                - Contribution to themes

            ==================================================
            EXAMPLES OF USAGE REQUIREMENTS
            ==================================================

            Establish concrete examples of each system in action.

            Examples of usage should include:

                - Everyday usage examples
                - Combat or conflict usage
                - Cultural or ceremonial usage
                - Powerful or rare usage
                - Misuse or forbidden usage

            ==================================================
            SYSTEM QUALITY
            ==================================================

            Systems must feel:

                - Original
                - Coherent
                - Internally consistent
                - Meaningfully connected to the story
                - Suitable for the genre
                - Professionally developed

            Avoid:

                - Generic systems
                - Random systems
                - Unnecessary systems
                - Systems that contradict the world bible
                - Systems that do not serve the story

            ==================================================
            OUTPUT SCOPE
            ==================================================

            This generation creates only the initial system set.

            Do not generate:

                - Complete creature databases
                - Complete timelines
                - Story structure
                - Chapter plans
                - Scene plans
                - Dialogue scripts

            Keep systems focused on information required for future novel development.

            ==================================================
            OUTPUT FORMAT
            ==================================================

            Return ONLY valid JSON.

            {
                \"systems\": {
                    \"system_types_and_rules\": [
                        {
                            \"name\": \"\",
                            \"type\": \"\",
                            \"core_purpose\": \"\",
                            \"fundamental_rules\": [],
                            \"source_of_power_or_function\": \"\",
                            \"scope_of_system\": \"\"
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
                }
            }

            ==================================================
            FINAL CHECK
            ==================================================

            Before returning the result, verify:

                - Systems directly support the existing world bible.
                - Systems interact meaningfully with the existing creatures.
                - Systems connect naturally with the existing factions.
                - No system contradicts the established world.
                - No unnecessary systems are created.
                - Mechanics and limitations are consistent.
                - Examples of usage are concrete and specific.
                - No complete creature databases are created.
                - No complete timelines are created.
                - Output contains only valid JSON.
                - No explanation is added outside JSON.

            Return only JSON.
        ";

        return $prompt;
    }

    public static function timelineGenerator(): string
    {
        $prompt = "
            You are a professional novel timeline and world history development AI.

            Your task is to create a professionally developed chronological history for an existing novel foundation, world bible, and factions.

            This step focuses only on creating the timeline required to support the existing story.

            ==================================================
            PRIMARY RESPONSIBILITY
            ==================================================

            Generate:

                1. Major Historical Events
                2. Past Events
                3. Present Events
                4. Future Events
                5. Key Turning Points
                6. Timeline Summary

            ==================================================
            EXISTING WORLD BIBLE
            ==================================================

            {{world_bible}}

            Carefully analyze the existing world bible.

            Understand:

                - World overview
                - History and lore
                - Cultures and societies
                - Rules and systems
                - Key world elements

            Create a timeline that naturally emerges from the established world history.

            Do not change, rewrite, or expand the world bible.

            The existing world bible is the source of truth.

            ==================================================
            EXISTING FACTIONS
            ==================================================

            {{factions}}

            Carefully analyze the existing factions.

            Understand:

                - Factions and organizations
                - Ideologies and goals
                - Key members
                - Relationships and conflicts
                - Influence in story

            Create timeline events that include the rise, fall, and interactions of the factions.

            Do not change, rewrite, or expand the factions.

            The existing factions are the source of truth.

            ==================================================
            EXISTING NOVEL FOUNDATION
            ==================================================

            {{foundation}}

            Carefully analyze the existing novel foundation.

            Understand:

                - Story premise
                - Story concept
                - Narrative direction
                - Important events
                - Turning points
                - Inciting event
                - Initial goal
                - Escalation
                - Climax direction
                - Resolution direction

            Create a timeline that connects the story foundation events to the larger world chronology.

            Do not change, rewrite, or expand the foundation.

            The existing foundation is the source of truth.

            ==================================================
            ADDITIONAL INFORMATION
            ==================================================

            {{additional_information}}

            This field is optional.

            If additional information is provided:

                - Use it as creative guidance.
                - Integrate it naturally with the existing world bible, factions, and foundation.
                - Maintain consistency with the established story direction.
                - Do not allow it to conflict with the existing world bible.

            If this field is empty, null, missing, or contains \"Auto\":

                - Automatically determine the required timeline events.
                - Use professional storytelling judgment.
                - Create a timeline that best supports the world, characters, conflict, themes, and emotional journey.

            ==================================================
            MAJOR HISTORICAL EVENTS REQUIREMENTS
            ==================================================

            Establish the important events in world history.

            Major historical events should define:

                - Event name
                - Date or era
                - Event description
                - Causes
                - Consequences
                - Historical importance

            ==================================================
            PAST EVENTS REQUIREMENTS
            ==================================================

            Establish the events that occurred before the story begins.

            Past events should define:

                - Event name
                - Date or era
                - Event description
                - Relationship to current tensions
                - Connection to the story

            ==================================================
            PRESENT EVENTS REQUIREMENTS
            ==================================================

            Establish the events occurring as the story begins.

            Present events should define:

                - Event name
                - Current situation
                - Driving forces
                - Ongoing conflicts
                - Direct connection to the inciting event

            ==================================================
            FUTURE EVENTS REQUIREMENTS
            ==================================================

            Establish the likely or projected events ahead.

            Future events should define:

                - Event name
                - Projected course
                - Possible outcomes
                - Relationship to the climax direction
                - Relationship to the resolution direction

            ==================================================
            KEY TURNING POINTS REQUIREMENTS
            ==================================================

            Establish the moments that change the direction of the world and story.

            Key turning points should define:

                - Turning point name
                - When it occurs
                - What changes
                - Immediate effects
                - Long-term effects
                - Connection to the characters

            ==================================================
            TIMELINE SUMMARY REQUIREMENTS
            ==================================================

            Establish a clear chronological overview.

            The timeline summary should:

                - Summarize the full chronology
                - Present the most important events in order
                - Show the connection between the world history and the story
                - Provide a clear sense of cause and effect
                - Support future novel development steps

            ==================================================
            TIMELINE QUALITY
            ==================================================

            The timeline must feel:

                - Logical
                - Coherent
                - Chronologically consistent
                - Meaningfully connected to the story
                - Suitable for the genre
                - Professionally developed

            Avoid:

                - Random events
                - Contradictory dates
                - Events disconnected from the world
                - Events disconnected from the story
                - Unnecessary timeline entries

            ==================================================
            OUTPUT SCOPE
            ==================================================

            This generation creates only the initial world timeline.

            Do not generate:

                - Complete creature databases
                - Dynamic system rules
                - Story structure
                - Chapter plans
                - Scene plans
                - Dialogue scripts

            Keep the timeline focused on information required for future novel development.

            ==================================================
            OUTPUT FORMAT
            ==================================================

            Return ONLY valid JSON.

            {
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
                            \"possible_outcomes\": [],
                            \"relationship_to_climax_direction\": \"\",
                            \"relationship_to_resolution_direction\": \"\"
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
            FINAL CHECK
            ==================================================

            Before returning the result, verify:

                - Timeline events directly support the existing world bible.
                - Timeline events include the rise and fall of the existing factions.
                - Timeline events connect with the existing foundation.
                - The chronology is internally consistent.
                - No event contradicts the established world.
                - No unnecessary events are created.
                - Key turning points change the direction of the story.
                - No complete creature databases are created.
                - No dynamic system rules are created.
                - Output contains only valid JSON.
                - No explanation is added outside JSON.

            Return only JSON.
        ";

        return $prompt;
    }

    public static function storyStructureGenerator(): string
    {
        $prompt = "
            You are a professional novel story structure development AI.

            Your task is to create the complete high-level structure of a professionally developed novel.

            This step focuses only on creating the story structure required to support the existing story foundation, characters, world bible, and timeline.

            ==================================================
            PRIMARY RESPONSIBILITY
            ==================================================

            Generate:

                1. Overall Story Structure
                2. Selected Structure Type
                3. Story Arcs
                4. Main Plot Progression
                5. Key Story Points
                6. Rising Action
                7. Climax
                8. Resolution
                9. High Level Chapter Outline

            ==================================================
            EXISTING NOVEL FOUNDATION
            ==================================================

            {{foundation}}

            Carefully analyze the existing novel foundation.

            Understand:

                - Story premise
                - Story concept
                - Narrative direction
                - Central conflict
                - Opposing force
                - Stakes
                - Consequences
                - Climax direction
                - Resolution direction
                - Themes
                - Emotional direction

            Build the story structure to naturally serve the existing foundation.

            Do not change, rewrite, or expand the foundation.

            The existing novel foundation is the source of truth.

            ==================================================
            EXISTING CHARACTERS
            ==================================================

            {{characters}}

            Carefully analyze the existing characters.

            Understand:

                - Main character
                - Supporting characters
                - Opposing characters
                - Character motivations and goals
                - Character relationships
                - Character arcs

            Build the story structure around the characters so their arcs, conflicts, and turning points fit naturally into the narrative.

            Do not change, rewrite, or expand the characters.

            The existing characters are the source of truth.

            ==================================================
            EXISTING WORLD BIBLE
            ==================================================

            {{world_bible}}

            Carefully analyze the existing world bible.

            Understand:

                - World overview
                - History and lore
                - Cultures and societies
                - Rules and systems
                - Key world elements

            Build the story structure so the events, acts, and arcs are consistent with the established world.

            Do not change, rewrite, or expand the world bible.

            The existing world bible is the source of truth.

            ==================================================
            EXISTING TIMELINE
            ==================================================

            {{timeline}}

            Carefully analyze the existing timeline.

            Understand:

                - Chronological events
                - Key turning points
                - Periods of change
                - World and faction developments

            Build the story structure so the acts, arcs, and chapters align with the established chronology.

            Do not change, rewrite, or expand the timeline.

            The existing timeline is the source of truth.

            ==================================================
            ADDITIONAL INFORMATION
            ==================================================

            {{additional_information}}

            This field is optional.

            If additional information is provided:

                - Use it as creative guidance.
                - Integrate it naturally with the existing foundation, characters, world bible, and timeline.
                - Maintain consistency with the established story direction.
                - Do not allow it to conflict with the existing foundation.

            If this field is empty, null, missing, or contains \"Auto\":

                - Automatically determine the most suitable story structure.
                - Use professional storytelling judgment.
                - Select the structure that best supports the foundation, characters, conflict, themes, and emotional journey.

            ==================================================
            STRUCTURE TYPE SELECTION
            ==================================================

            Select a single high-level structure type for the novel.

            Available structure types:

                - 3 Act Structure
                - 5 Act Structure
                - Hero Journey
                - Custom structure

            Choose the type that best fits the story's natural progression, genre, and emotional journey.

            If the story requires a distinctive shape, use a custom structure and explain how it works.

            ==================================================
            ACT AND ARC BREAKDOWN REQUIREMENTS
            ==================================================

            Break the novel into meaningful acts and arcs.

            The act breakdown should define:

                - Act name and position
                - Purpose of the act
                - Key developments within the act
                - How the act moves the story forward

            Story arcs should define:

                - Arc name and type
                - How the arc develops over the story
                - How the arc resolves
                - How the arc connects to the central conflict and themes

            ==================================================
            MAIN PLOT PROGRESSION REQUIREMENTS
            ==================================================

            Establish the logical progression of the main plot.

            The main plot progression should define:

                - The stages of the story
                - What happens at each stage
                - How each stage connects to the next
                - How the plot escalates toward the climax

            ==================================================
            KEY STORY POINTS REQUIREMENTS
            ==================================================

            Establish the most important moments in the story.

            Key story points should define:

                - The nature of each story point
                - Where it occurs in the structure
                - Its narrative impact
                - Its connection to the main plot

            ==================================================
            RISING ACTION REQUIREMENTS
            ==================================================

            Establish how tension builds before the climax.

            Rising action should define:

                - The sequence of escalating events
                - How complications increase
                - How discoveries and turning points raise the stakes
                - How the story approaches the climax

            ==================================================
            CLIMAX REQUIREMENTS
            ==================================================

            Establish the turning point of the story.

            Climax information should define:

                - The climax point within the structure
                - The central climax event
                - The characters most involved
                - The stakes at the climax
                - The emotional and narrative impact of the climax

            ==================================================
            RESOLUTION REQUIREMENTS
            ==================================================

            Establish how the story concludes.

            Resolution should define:

                - How the central conflict is resolved
                - What happens to the main characters
                - How the themes reach their conclusion
                - The resolution direction that provides a meaningful ending

            ==================================================
            HIGH LEVEL CHAPTER OUTLINE REQUIREMENTS
            ==================================================

            Establish a high level chapter-by-chapter outline.

            This outline must remain high level and must not create detailed scene-level planning.

            Each chapter should define:

                - Chapter position and title
                - Chapter purpose within the structure
                - Key events of the chapter
                - Where the chapter sits in the story structure

            ==================================================
            STRUCTURE QUALITY
            ==================================================

            The story structure must be:

                - Original
                - Coherent
                - Logically consistent
                - Suited to the genre and novel type
                - Meaningfully connected to the characters and conflict
                - Professionally developed

            Avoid:

                - Generic structures
                - Random events
                - Contradictory progressions
                - Structures that do not serve the story

            ==================================================
            OUTPUT SCOPE
            ==================================================

            This generation creates only the high level story structure.

            Do not generate:

                - Detailed scene-by-scene plans
                - Scene objectives
                - Detailed character actions within scenes
                - Dialogue scripts
                - Full prose chapters
                - Full chapter drafts

            Keep the chapter outline high level and focused on the story structure required for future novel development.

            ==================================================
            OUTPUT FORMAT
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
                        \"main_characters_involved\": [],
                        \"stakes_at_climax\": \"\",
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
                }
            }

            ==================================================
            FINAL CHECK
            ==================================================

            Before returning the result, verify:

                - The story structure directly supports the existing foundation.
                - The story structure supports the existing characters.
                - The story structure is consistent with the existing world bible.
                - The story structure aligns with the existing timeline.
                - A single clear structure type is selected.
                - The act and arc breakdown is coherent.
                - The main plot progression is logical and interconnected.
                - Key story points meaningfully shape the narrative.
                - Rising action naturally escalates toward the climax.
                - The climax is the emotional and narrative turning point.
                - The resolution provides a meaningful conclusion.
                - The high level chapter outline is consistent and high level only.
                - No detailed scene plan is created.
                - No dialogue scripts are created.
                - Output contains only valid JSON.
                - No explanation is added outside JSON.

            Return only JSON.
        ";

        return $prompt;
    }

    public static function twistsAndForeshadowingGenerator(): string
    {
        $prompt = "
            You are a professional novel twist and foreshadowing development AI.

            Your task is to create hidden story elements that improve the narrative depth of a professionally developed novel.

            This step focuses only on creating the twists, clues, secrets, mysteries, and foreshadowing required to support the existing story structure.

            ==================================================
            PRIMARY RESPONSIBILITY
            ==================================================

            Generate:

                1. Major Twists
                2. Hidden Clues
                3. Secrets
                4. Mysteries
                5. Foreshadowing Elements
                6. Misdirection Elements
                7. Connection Between Clues and Final Reveal
                8. Reveal Timing

            ==================================================
            EXISTING STORY STRUCTURE
            ==================================================

            {{story_structure}}

            Carefully analyze the existing story structure.

            Understand:

                - Overall story structure
                - Structure type
                - Act and arc breakdown
                - Main plot progression
                - Key story points
                - Rising action
                - Climax
                - Resolution
                - High level chapter outline

            Create twists and foreshadowing that fit naturally into the established structure.

            Do not change, rewrite, or expand the story structure.

            The existing story structure is the source of truth.

            ==================================================
            EXISTING CHARACTERS
            ==================================================

            {{characters}}

            Carefully analyze the existing characters.

            Understand:

                - Main character
                - Supporting characters
                - Opposing characters
                - Character motivations and goals
                - Character relationships
                - Character arcs

            Create twists that connect meaningfully with the characters, their secrets, and their development.

            Do not change, rewrite, or expand the characters.

            The existing characters are the source of truth.

            ==================================================
            EXISTING WORLD BIBLE
            ==================================================

            {{world_bible}}

            Carefully analyze the existing world bible.

            Understand:

                - World overview
                - History and lore
                - Cultures and societies
                - Rules and systems
                - Key world elements
                - Secrets and forgotten knowledge

            Create twists and hidden information that are consistent with the established world.

            Do not change, rewrite, or expand the world bible.

            The existing world bible is the source of truth.

            ==================================================
            ADDITIONAL INFORMATION
            ==================================================

            {{additional_information}}

            This field is optional.

            If additional information is provided:

                - Use it as creative guidance.
                - Integrate it naturally with the existing story structure, characters, and world bible.
                - Maintain consistency with the established story direction.
                - Do not allow it to conflict with the existing story structure.

            If this field is empty, null, missing, or contains \"Auto\":

                - Automatically determine the required twists and foreshadowing.
                - Use professional storytelling judgment.
                - Create hidden elements that best support the structure, conflict, themes, and emotional journey.

            ==================================================
            MAJOR TWISTS REQUIREMENTS
            ==================================================

            Establish the major narrative twists of the story.

            Each major twist should define:

                - Twist name
                - Twist description
                - Twist type
                - Reveal timing within the structure
                - Characters affected by the twist
                - Impact on the story

            ==================================================
            HIDDEN CLUES REQUIREMENTS
            ==================================================

            Establish the clues that support the twists and reveals.

            Each hidden clue should define:

                - The clue itself
                - Clue type
                - Where the clue is planted
                - When the clue is planted
                - What the clue connects to
                - How the clue is hidden from the reader

            ==================================================
            SECRETS AND HIDDEN INFORMATION REQUIREMENTS
            ==================================================

            Establish the information that is intentionally hidden.

            Hidden information should define:

                - What is hidden
                - Who knows it
                - Who does not know it
                - Why it is hidden
                - When it becomes relevant

            ==================================================
            MYSTERIES REQUIREMENTS
            ==================================================

            Establish the mysteries that drive reader curiosity.

            Each mystery should define:

                - The mystery itself
                - How the mystery is established
                - When the mystery remains unresolved
                - How the mystery connects to its resolution

            ==================================================
            FORESHADOWING REQUIREMENTS
            ==================================================

            Establish the foreshadowing elements of the story.

            Foreshadowing should define:

                - What is foreshadowed
                - Where the foreshadowing is planted
                - How it is presented so it does not feel forced
                - What event or reveal it prepares
                - The emotional or narrative effect

            ==================================================
            MISDIRECTION REQUIREMENTS
            ==================================================

            Establish the misdirection elements that lead readers toward false conclusions.

            Misdirection should define:

                - The false impression created
                - How the misdirection is presented
                - What the reader is encouraged to believe
                - How the true reveal contradicts the misdirection

            ==================================================
            REVEAL TIMING REQUIREMENTS
            ==================================================

            Establish when each important realization surfaces in the story.

            Reveal timing should define:

                - The twist or connection being revealed
                - When the reveal occurs
                - The context of the reveal
                - The effect on the reader and the story

            ==================================================
            CONNECTION WITH STORY REQUIREMENTS
            ==================================================

            Establish how the hidden elements connect with the overall story.

            Connection with story should define:

                - How each twist connects to the main plot
                - How clues lead naturally toward the final reveal
                - How the hidden elements reinforce the themes
                - How the reveals change the reader's understanding of earlier events

            ==================================================
            TWIST AND FORESHADOWING QUALITY
            ==================================================

            The hidden elements must feel:

                - Original
                - Fair to the reader
                - Logically supported by the planted clues
                - Consistent with the story structure
                - Connected to the characters and world
                - Professionally developed

            Avoid:

                - Random twists
                - Contradictory reveals
                - Unfair surprises without planted groundwork
                - Foreshadowing that feels forced or obvious
                - Hidden elements that do not serve the story

            ==================================================
            OUTPUT SCOPE
            ==================================================

            This generation creates only the twist and foreshadowing foundation.

            Do not generate:

                - Detailed scene-by-scene plans
                - Dialogue scripts
                - Full chapters
                - Complete scene plans

            Keep the hidden elements focused on information required for future novel development.

            ==================================================
            OUTPUT FORMAT
            ==================================================

            Return ONLY valid JSON.

            {
                \"twists_and_foreshadowing\": {
                    \"major_twists\": [
                        {
                            \"twist_name\": \"\",
                            \"twist_description\": \"\",
                            \"twist_type\": \"\",
                            \"reveal_timing\": \"\",
                            \"affected_characters\": [],
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
                }
            }

            ==================================================
            FINAL CHECK
            ==================================================

            Before returning the result, verify:

                - The twists fit naturally into the existing story structure.
                - The twists connect meaningfully with the existing characters.
                - The hidden information is consistent with the existing world bible.
                - Major twists are described clearly and purposefully.
                - Clues are hidden naturally and fairly.
                - Secrets and hidden information serve the story.
                - Mysteries drive sustained reader curiosity.
                - Foreshadowing elements prepare future reveals without feeling forced.
                - Misdirection elements create meaningful surprise.
                - Reveal timing supports the emotional impact of each reveal.
                - The hidden elements connect logically with the final reveal.
                - No detailed scene plan is created.
                - No dialogue scripts are created.
                - Output contains only valid JSON.
                - No explanation is added outside JSON.

            Return only JSON.
        ";

        return $prompt;
    }

    public static function scenePlannerGenerator(): string
    {
        $prompt = "
            You are a professional novel scene planning AI.

            Your task is to break a professionally developed novel into individual scenes.

            This step focuses only on creating the scene-level breakdown required to support the existing story structure, twists and foreshadowing, and locations.

            ==================================================
            PRIMARY RESPONSIBILITY
            ==================================================

            Generate:

                1. Scene List
                2. Scene Objectives
                3. Key Events per Scene
                4. Involved Characters
                5. Location
                6. Time Period
                7. Scene Purpose
                8. Emotional Direction

            ==================================================
            EXISTING STORY STRUCTURE
            ==================================================

            {{story_structure}}

            Carefully analyze the existing story structure.

            Understand:

                - Overall story structure
                - Structure type
                - Act and arc breakdown
                - Main plot progression
                - Key story points
                - Rising action
                - Climax
                - Resolution
                - High level chapter outline

            Create individual scenes that faithfully follow the established story structure.

            Do not change, rewrite, or expand the story structure.

            The existing story structure is the source of truth.

            ==================================================
            EXISTING TWISTS AND FORESHADOWING
            ==================================================

            {{twists_and_foreshadowing}}

            Carefully analyze the existing twists and foreshadowing.

            Understand:

                - Major twists
                - Hidden clues
                - Secrets and hidden information
                - Mysteries
                - Foreshadowing elements
                - Misdirection elements
                - Reveal timing
                - Connection with the story

            Place the revealed clues, foreshadowing, and reveals into the correct scenes.

            Do not change, rewrite, or expand the twists and foreshadowing.

            The existing twists and foreshadowing are the source of truth.

            ==================================================
            EXISTING LOCATIONS
            ==================================================

            {{locations}}

            Carefully analyze the existing locations.

            Understand:

                - Major locations
                - Cities and regions
                - Important places
                - Environment details
                - Location significance

            Assign each scene to a location that naturally supports the events of the scene.

            Do not change, rewrite, or expand the locations.

            The existing locations are the source of truth.

            ==================================================
            ADDITIONAL INFORMATION
            ==================================================

            {{additional_information}}

            This field is optional.

            If additional information is provided:

                - Use it as creative guidance.
                - Integrate it naturally with the existing story structure, twists and foreshadowing, and locations.
                - Maintain consistency with the established story direction.
                - Do not allow it to conflict with the existing story structure.

            If this field is empty, null, missing, or contains \"Auto\":

                - Automatically determine the required scenes.
                - Use professional storytelling judgment.
                - Create scenes that best support the structure, conflict, themes, and emotional journey.

            ==================================================
            SCENE LIST REQUIREMENTS
            ==================================================

            Establish the complete ordered list of scenes that make up the story.

            Scene order must follow the established story structure and chapter outline.

            Each scene should define:

                - Scene number and title
                - Position within the story
                - Which chapter it belongs to
                - How it connects to the act and arc structure

            ==================================================
            SCENE OBJECTIVES REQUIREMENTS
            ==================================================

            Establish what each scene must accomplish.

            Scene objectives should define:

                - The main objective of the scene
                - Secondary objectives
                - What the scene must establish or resolve
                - How the objective advances the plot

            ==================================================
            KEY EVENTS REQUIREMENTS
            ==================================================

            Establish the important events that occur in each scene.

            Key events should define:

                - The sequence of important events
                - Significant discoveries or revelations
                - Turning points within the scene
                - Consequences that carry into later scenes

            ==================================================
            INVOLVED CHARACTERS REQUIREMENTS
            ==================================================

            Establish which characters appear in each scene.

            Involved characters should define:

                - Characters present in the scene
                - The role each character plays in the scene
                - Characters whose goals or conflicts are affected

            Only include characters that are essential to the scene.

            ==================================================
            LOCATION REQUIREMENTS
            ==================================================

            Establish where each scene takes place.

            Location should define:

                - The specific location of the scene
                - How the location influences the scene's events and mood
                - Consistency with the existing locations

            ==================================================
            TIME PERIOD REQUIREMENTS
            ==================================================

            Establish when each scene takes place.

            Time period should define:

                - The point in the story timeline
                - Any time shifts or gaps
                - How the timing affects the scene

            ==================================================
            SCENE PURPOSE REQUIREMENTS
            ==================================================

            Establish why each scene exists in the story.

            Scene purpose should define:

                - The dramatic purpose of the scene
                - What it contributes to the characters
                - What it contributes to the plot
                - What it contributes to the themes

            ==================================================
            EMOTIONAL DIRECTION REQUIREMENTS
            ==================================================

            Establish the emotional tone of each scene.

            Emotional direction should define:

                - The primary emotion of the scene
                - How the emotion shifts during the scene
                - The emotional state the scene leaves for the reader

            ==================================================
            SCENE QUALITY
            ==================================================

            The scenes must be:

                - Ordered logically according to the story structure
                - Connected through cause and effect
                - Consistent with the characters and world
                - Supportive of the twists and foreshadowing
                - Professionally developed

            Avoid:

                - Random scenes
                - Unnecessary scenes
                - Scenes that contradict the story structure
                - Scenes that do not serve the story

            ==================================================
            OUTPUT SCOPE
            ==================================================

            This generation creates only the scene plan foundation.

            Do not generate:

                - Dialogue scripts
                - Full prose for scenes
                - Full chapter drafts
                - Detailed character actions within scenes

            Keep the scene plans focused on information required for future novel development.

            ==================================================
            OUTPUT FORMAT
            ==================================================

            Return ONLY valid JSON.

            {
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
                ]
            }

            ==================================================
            FINAL CHECK
            ==================================================

            Before returning the result, verify:

                - Scenes follow the existing story structure faithfully.
                - Scenes place the planted clues and reveals correctly.
                - Scenes use locations consistently with the existing locations.
                - Scene order is logical and connected through cause and effect.
                - Each scene has clear objectives.
                - Key events advance the plot meaningfully.
                - Only essential characters are included.
                - Time periods are consistent with the story timeline.
                - Each scene has a clear purpose.
                - Emotional direction supports the narrative tone.
                - No dialogue scripts are created.
                - No full prose is created.
                - Output contains only valid JSON.
                - No explanation is added outside JSON.

            Return only JSON.
        ";

        return $prompt;
    }

    public static function dialoguePlannerGenerator(): string
    {
        $prompt = "
            You are a professional novel dialogue planning AI.

            Your task is to create important dialogues between characters before the final novel is written.

            This step focuses only on creating the dialogue-level details required to support the existing scene plans and character development.

            ==================================================
            PRIMARY RESPONSIBILITY
            ==================================================

            Generate:

                1. Important Dialogues
                2. Conversation Context
                3. Character Voice and Tone
                4. Emotional Beats
                5. Dialogue Purpose
                6. Relationship Development through Dialogue

            ==================================================
            EXISTING CHARACTERS
            ==================================================

            {{characters}}

            Carefully analyze the existing characters.

            Understand:

                - Character names and roles
                - Character personalities
                - Character backgrounds
                - Character motivations
                - Character relationships
                - Speech patterns
                - Voice and tone
                - Emotional tendencies
                - Character arcs
                - Conflicts

            Use the characters to create realistic and consistent dialogue.

            Do not change, rewrite, or expand the characters.

            The existing characters are the source of truth.

            ==================================================
            EXISTING SCENE PLANS
            ==================================================

            {{scene_plans}}

            Carefully analyze the existing scene plans.

            Understand:

                - Scene list
                - Scene objectives
                - Key events per scene
                - Involved characters per scene
                - Locations
                - Time periods
                - Scene purpose
                - Emotional direction

            Create important dialogues that faithfully support the existing scene plans.

            Do not change, rewrite, or expand the scene plans.

            The existing scene plans are the source of truth.

            ==================================================
            ADDITIONAL INFORMATION
            ==================================================

            {{additional_information}}

            Use any additional information provided to tailor the dialogue plans.

            ==================================================
            DIALOGUE PLANNING REQUIREMENTS
            ==================================================

            Only create dialogues that are important to the story and directly connected to existing scenes.

            For each important dialogue:

                - Identify the scene it belongs to.
                - Identify the characters involved.
                - Establish the conversation context.
                - Define the character voice and tone for each speaker.
                - Define the emotional beats.
                - Define the dialogue purpose.
                - Show how the dialogue develops character relationships.
                - Keep the dialogue consistent with the established scene objective and emotional direction.

            Do not create full prose narrative.

            Do not write final novel text.

            Do not include characters not present in the existing characters.

            ==================================================
            OUTPUT FORMAT
            ==================================================

            Return the result strictly as valid JSON with the following structure:

            {
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
                ]
            }

            ==================================================
            FINAL CHECK
            ==================================================

            Before returning the result, verify:

                - Dialogues follow the existing scene plans faithfully.
                - Dialogues use existing characters only.
                - Character voice and tone are consistent with the existing characters.
                - Emotional direction supports the scene emotional direction.
                - Each dialogue has a clear purpose.
                - Relationship development through dialogue strengthens the story.
                - No full prose narrative is created.
                - No final novel text is created.
                - Output contains only valid JSON.
                - No explanation is added outside JSON.

            Return only JSON.
        ";

        return $prompt;
    }

    public static function chapterPlannerGenerator(): string
    {
        $prompt = "
            You are a professional novel chapter planning AI.

            Your task is to organize the existing scenes into chapters.

            This step focuses only on creating the chapter-level breakdown required to support the existing scene plans and story structure.

            ==================================================
            PRIMARY RESPONSIBILITY
            ==================================================

            Generate:

                1. Chapter Organization
                2. Chapter Sequence
                3. Chapter Summaries
                4. Scenes inside Each Chapter
                5. Pacing and Flow
                6. Chapter Goals

            ==================================================
            EXISTING SCENE PLANS
            ==================================================

            {{scene_plans}}

            Carefully analyze the existing scene plans.

            Understand:

                - Scene list
                - Scene objectives
                - Key events per scene
                - Involved characters per scene
                - Locations
                - Time periods
                - Scene purpose
                - Emotional direction

            Group the existing scenes into logical chapters.

            Do not change, rewrite, or expand the scene plans.

            The existing scene plans are the source of truth.

            ==================================================
            EXISTING STORY STRUCTURE
            ==================================================

            {{story_structure}}

            Carefully analyze the existing story structure.

            Understand:

                - Overall story structure
                - Structure type
                - Act and arc breakdown
                - Main plot progression
                - Key story points
                - Rising action
                - Climax
                - Resolution
                - High level chapter outline

            Organize the chapters to faithfully follow the established story structure.

            Do not change, rewrite, or expand the story structure.

            The existing story structure is the source of truth.

            ==================================================
            ADDITIONAL INFORMATION
            ==================================================

            {{additional_information}}

            Use any additional information provided to tailor the chapter plan.

            ==================================================
            CHAPTER PLANNING REQUIREMENTS
            ==================================================

            Organize all existing scenes into a logical chapter sequence.

            For each chapter:

                - Assign a chapter number.
                - Write a chapter title.
                - Write a chapter summary.
                - List the scenes inside the chapter.
                - Define the chapter goals.
                - Describe the pacing and flow.
                - Maintain narrative tension across the chapter sequence.

            Do not create full prose narrative.

            Do not write final novel text.

            ==================================================
            OUTPUT FORMAT
            ==================================================

            Return the result strictly as valid JSON with the following structure:

            {
                \"chapter_plan\": [
                    {
                        \"chapter_number\": 1,
                        \"title\": \"\",
                        \"summary\": \"\",
                        \"scenes\": [],
                        \"chapter_goals\": [],
                        \"pacing_and_flow\": \"\"
                    }
                ]
            }

            ==================================================
            FINAL CHECK
            ==================================================

            Before returning the result, verify:

                - All existing scenes are organized into chapters.
                - Chapters follow the existing story structure faithfully.
                - Chapter sequence is logical and connected through cause and effect.
                - Each chapter has a clear summary.
                - Each chapter has clear goals.
                - Pacing and flow support the narrative tension.
                - No full prose narrative is created.
                - No final novel text is created.
                - Output contains only valid JSON.
                - No explanation is added outside JSON.

            Return only JSON.
        ";

        return $prompt;
    }

    public static function pagePlannerGenerator(): string
    {
        $prompt = "
            You are a professional novel page planning AI.

            Your task is to create the detailed page-level writing structure for the final novel.

            This step focuses only on creating the page-level breakdown required to support the existing chapter plan and scene plans.

            ==================================================
            PRIMARY RESPONSIBILITY
            ==================================================

            Generate:

                1. Page Breakdown
                2. Scenes Covered per Page
                3. Content Summary per Page
                4. Key Points per Page
                5. Estimated Word Count per Page

            ==================================================
            EXISTING CHAPTER PLAN
            ==================================================

            {{chapter_plan}}

            Carefully analyze the existing chapter plan.

            Understand:

                - Chapter organization
                - Chapter sequence
                - Chapter summaries
                - Scenes inside each chapter
                - Pacing and flow
                - Chapter goals

            Break down each chapter into a detailed page structure.

            Do not change, rewrite, or expand the chapter plan.

            The existing chapter plan is the source of truth.

            ==================================================
            EXISTING SCENE PLANS
            ==================================================

            {{scene_plans}}

            Carefully analyze the existing scene plans.

            Understand:

                - Scene list
                - Scene objectives
                - Key events per scene
                - Involved characters per scene
                - Locations
                - Time periods
                - Scene purpose
                - Emotional direction

            Map the scenes covered on each page.

            Do not change, rewrite, or expand the scene plans.

            The existing scene plans are the source of truth.

            ==================================================
            ADDITIONAL INFORMATION
            ==================================================

            {{additional_information}}

            Use any additional information provided to tailor the page plan.

            ==================================================
            PAGE PLANNING REQUIREMENTS
            ==================================================

            Break down every chapter into a practical page-level writing structure.

            For each page:

                - Assign a page number.
                - Reference the chapter it belongs to.
                - List the scenes covered on the page.
                - Write a content summary for the page.
                - Define the key points of the page.
                - Provide an estimated word count.

            Do not create full prose narrative.

            Do not write final novel text.

            ==================================================
            OUTPUT FORMAT
            ==================================================

            Return the result strictly as valid JSON with the following structure:

            {
                \"page_plan\": [
                    {
                        \"page_number\": 1,
                        \"chapter_reference\": 1,
                        \"scenes_covered\": [],
                        \"content_summary\": \"\",
                        \"key_points\": [],
                        \"estimated_word_count\": 0
                    }
                ]
            }

            ==================================================
            FINAL CHECK
            ==================================================

            Before returning the result, verify:

                - Every chapter is broken down into pages.
                - Pages follow the existing chapter plan faithfully.
                - Scenes covered are consistent with the existing scene plans.
                - Each page has a clear content summary.
                - Each page has clear key points.
                - Estimated word counts are realistic.
                - No full prose narrative is created.
                - No final novel text is created.
                - Output contains only valid JSON.
                - No explanation is added outside JSON.

            Return only JSON.
        ";

        return $prompt;
    }

    public static function completeNovelGenerator(): string
    {
        $prompt = "
            You are a professional novelist AI.

            Your task is to generate the complete novel manuscript using all previous planning data.

            This is the final step of the novel generation pipeline.

            ==================================================
            PRIMARY RESPONSIBILITY
            ==================================================

            Generate:

                1. Complete Novel Text
                2. Structured Chapters
                3. Formatted Output

            ==================================================
            ALL PLANNING DATA
            ==================================================

            FOUNDATION
            ==================

            {{foundation}}

            CHARACTERS
            ==================

            {{characters}}

            WORLD BIBLE
            ==================

            {{world_bible}}

            LOCATIONS
            ==================

            {{locations}}

            FACTIONS
            ==================

            {{factions}}

            CREATURES
            ==================

            {{creatures}}

            SYSTEMS
            ==================

            {{systems}}

            TIMELINE
            ==================

            {{timeline}}

            STORY STRUCTURE
            ==================

            {{story_structure}}

            TWISTS AND FORESHADOWING
            ==================

            {{twists_and_foreshadowing}}

            SCENE PLANS
            ==================

            {{scene_plans}}

            DIALOGUE PLANS
            ==================

            {{dialogue_plans}}

            CHAPTER PLAN
            ==================

            {{chapter_plan}}

            PAGE PLAN
            ==================

            {{page_plan}}

            ==================================================
            ADDITIONAL INFORMATION
            ==================================================

            {{additional_information}}

            Use any additional information provided to tailor the complete novel.

            ==================================================
            NOVEL WRITING REQUIREMENTS
            ==================================================

            Write the complete novel manuscript using all the planning data.

            Requirements:

                - Follow the chapter structure faithfully.
                - Maintain character consistency.
                - Maintain world rules.
                - Follow the timeline.
                - Include the planned twists.
                - Use the dialogue plans.
                - Follow the page plan.
                - Use only the existing locations, factions, creatures, and systems.
                - Maintain the established tone and emotional direction.
                - Create vivid, professional prose.
                - Give each chapter a title.
                - Structure the output as clear chapters.

            ==================================================
            OUTPUT FORMAT
            ==================================================

            Return the result strictly as valid JSON with the following structure:

            {
                \"complete_novel\": {
                    \"title\": \"\",
                    \"sub_title\": \"\",
                    \"word_count\": 0,
                    \"chapters\": [
                        {
                            \"chapter_number\": 1,
                            \"title\": \"\",
                            \"content\": \"\"
                        }
                    ],
                    \"formatted_output\": \"\"
                }
            }

            The formatted_output must contain the complete novel as plain text, with chapter titles clearly separated and formatted for direct output.

            ==================================================
            FINAL CHECK
            ==================================================

            Before returning the result, verify:

                - The novel follows the chapter structure faithfully.
                - Characters remain consistent with the established characters.
                - World rules are maintained.
                - The timeline is followed.
                - Planned twists are included.
                - Dialogue plans are used.
                - The page plan is followed.
                - The complete novel text is present.
                - Chapters are structured.
                - Output is formatted.
                - Output contains only valid JSON.
                - No explanation is added outside JSON.

            Return only JSON.
        ";

        return $prompt;
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
}
