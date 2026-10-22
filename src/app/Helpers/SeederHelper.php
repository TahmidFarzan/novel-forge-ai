<?php
namespace App\Helpers;

use Illuminate\Support\Str;

class SeederHelper
{
    public static function audiences()
    {
        return collect([
            (object) [
                'name'               => 'Children',
                'brief'              => 'Content suitable for children under 12',
                'prompt_instruction' => 'Focus on age-appropriate themes including friendship, adventure, imagination, moral lessons, and curiosity. Planning should consider simple but meaningful conflicts, positive role models, safe environments, and clear moral outcomes. Language should be accessible and engaging, with vocabulary appropriate for young readers. Character planning should include relatable child protagonists, supportive adults, and peers who model positive behaviour. Story structure should have clear beginnings, escalating but manageable challenges, and satisfying resolutions that reinforce positive values. Avoid complex romantic subplots, graphic violence, disturbing content, or morally ambiguous endings. When combined with other genres, ensure all elements remain age-appropriate while maintaining the core genre elements.',
            ],

            (object) [
                'name'               => 'Young Adult',
                'brief'              => 'Content suitable for teenagers aged 12-18',
                'prompt_instruction' => 'Focus on themes of identity, self-discovery, independence, relationships, belonging, and navigating social pressures. Planning should consider teenage perspectives, authentic emotional experiences, coming-of-age challenges, peer dynamics, family tensions, and personal growth. Character planning should include protagonists who face real teenage concerns, make mistakes, learn from consequences, and develop stronger sense of self. Supporting characters should represent diverse perspectives and authentic teenage experiences. Story structure should balance internal emotional journeys with external conflicts, include relatable stakes, and provide resolutions that feel earned through character growth. Language should be engaging and authentic to teenage voice without being condescending. Content may include moderate themes but should avoid explicit material. When combined with other genres, maintain the adolescent perspective and coming-of-age elements throughout.',
            ],

            (object) [
                'name'               => 'Adult',
                'brief'              => 'Mature content suitable for adults aged 18 and above',
                'prompt_instruction' => 'Focus on complex themes, mature relationships, nuanced moral situations, and sophisticated narrative structures. Planning should consider adult perspectives, realistic consequences, complicated motivations, and themes that explore the full range of human experience. Character planning should include psychologically complex individuals with detailed backstories, realistic flaws, and multifaceted relationships. Supporting characters should have their own goals, conflicts, and development arcs. Story structure may employ non-linear timelines, multiple perspectives, ambiguous morality, and endings that challenge rather than comfort. Content may include explicit themes, violence, sexuality, substance use, and dark subject matter when they serve the narrative meaningfully. Language should be sophisticated and tailored to the genre conventions. When combined with other genres, fully integrate mature elements throughout while maintaining narrative coherence.',
            ],
        ]);
    }

    public static function novelTypes()
    {
        return collect([
            (object) [
                'name'               => 'Short Novel',
                'brief'              => 'Short-length novels with a focused but complete narrative structure',
                'prompt_instruction' => 'Develop the novel with a focused narrative scope that produces a minimum of 150 pages. The story must contain sufficient plot development, meaningful character arcs, important relationships, dialogue, descriptions, conflicts, discoveries, turning points, climax, and resolution to naturally support at least 150 pages. Maintain a complete novel structure with a clear beginning, developed middle, and satisfying conclusion. Keep the narrative focused by controlling unnecessary characters, locations, and subplots while ensuring the story does not feel rushed or incomplete. Do not reduce important development to make the story shorter, and do not use repetition, filler, artificial padding, or meaningless scenes only to increase page count.',
            ],

            (object) [
                'name'               => 'Medium Novel',
                'brief'              => 'Medium-length novels with balanced storytelling and deeper character development',
                'prompt_instruction' => 'Develop the novel with a broad and balanced narrative scope that produces a minimum of 300 pages. The story must contain substantial plot development, layered character arcs, meaningful relationships, rich dialogue, detailed descriptions, escalating conflicts, discoveries, turning points, climax, and resolution to naturally support at least 300 pages. Maintain a complete novel structure with a strong opening, a fully developed middle with multiple story threads, and a satisfying conclusion. Balance the narrative by allowing characters, locations, and subplots to develop progressively while keeping every element connected to the central story. Do not reduce important development to make the story shorter, and do not use repetition, filler, artificial padding, or meaningless scenes only to increase page count.',
            ],

            (object) [
                'name'               => 'Long Novel',
                'brief'              => 'Long-length novels with expansive plots, complex characters, and detailed development',
                'prompt_instruction' => 'Develop the novel with an expansive narrative scope that produces a minimum of 500 pages. The story must contain extensive plot development, complex and evolving character arcs, deep relationships, detailed dialogue, immersive descriptions, multiple conflicts, discoveries, turning points, climax, and resolution to naturally support at least 500 pages. Maintain a complete novel structure with a compelling opening, a rich and layered middle with interconnected story threads, and a comprehensive conclusion that resolves the narrative. Expand the narrative through meaningful character growth, interconnected subplots, and deeper world-building while keeping every element serving the central story. Do not reduce important development to make the story shorter, and do not use repetition, filler, artificial padding, or meaningless scenes only to increase page count.',
            ],
        ]);
    }

    public static function genres()
    {
        return collect([
            (object) [
                'name'               => 'Fantasy',
                'brief'              => 'Fantasy fiction with imaginary worlds and magic',
                'prompt_instruction' => 'Focus on building an invented world with consistent internal rules, a defined magic system with limitations and costs, memorable creatures and cultures, rich mythology, and conflicts that naturally emerge from the setting. World planning should consider geography, societies, power structures, laws of the world, magic sources and their consequences, technology level, mythology, and historical events that shape present tensions. Character planning should consider how characters are influenced by this world, how abilities and limitations create moral and practical challenges, and how personal goals connect with larger world conflicts. Story structure should ensure that fantastical elements escalate logically, stakes grow naturally, and the resolution remains consistent with the established world rules. When combined with other genres, integrate these fantasy world-building elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'Family Drama',
                'brief'              => 'Stories about family relationships and emotions',
                'prompt_instruction' => 'Focus on developing realistic family relationships, emotional conflicts, hidden tensions, and long-term bonds between family members. Planning should consider family structure, shared history, unresolved conflicts, generational differences, secrets, loyalty, resentment, and emotional expectations. Character planning should consider individual personalities, personal wounds, motivations, family roles, and how love and conflict exist together within relationships. Story structure should consider gradual emotional escalation, realistic conversations, meaningful turning points, and resolutions that address deeper family issues rather than only surface conflicts. When combined with other genres, integrate these family and emotional relationship elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'Romance',
                'brief'              => 'Love and relationship based stories',
                'prompt_instruction' => 'Focus on creating a believable central relationship built through emotional connection, attraction, vulnerability, and personal growth. Planning should consider what draws the characters together, what separates them, the obstacles preventing the relationship from developing, and how their connection changes over time. Character planning should consider distinct personalities, genuine chemistry, personal flaws, emotional needs, communication patterns, and individual growth required before commitment feels earned. Story structure should consider relationship development, emotional turning points, conflicts, intimate moments, and a satisfying resolution based on mutual understanding and growth rather than coincidence. When combined with other genres, integrate these relationship-development elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'Mystery',
                'brief'              => 'Mystery and investigation stories',
                'prompt_instruction' => 'Focus on creating a central mystery with a clear question, logical investigation, meaningful clues, believable suspects, and a solution supported by evidence. Planning should consider the hidden information, timeline of events, evidence chain, possible explanations, red herrings, and how readers can follow the investigation fairly. Character planning should consider investigators with personal reasons to solve the mystery, suspects with believable motives, witnesses with limited knowledge, and characters whose actions connect logically to the mystery. Story structure should carefully control the reveal of information, escalation of discoveries, investigative setbacks, final revelation, and complete explanation of the mystery. When combined with other genres, integrate these investigation and evidence-based elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'Thriller',
                'brief'              => 'Suspenseful and exciting stories',
                'prompt_instruction' => 'Focus on creating escalating tension, danger, uncertainty, psychological pressure, and conflicts that keep characters and readers under constant pressure. Planning should consider the central threat, hidden motives, increasing risks, unexpected developments, and how each event raises the stakes. Character planning should consider strong motivations, personal weaknesses, emotional pressure points, and opposing forces that create meaningful challenges. Story structure should consider pacing, suspense control, major reveals, reversals, rising danger, and a climax that resolves the central conflict in a satisfying way. When combined with other genres, integrate these suspense and tension-building elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'Horror',
                'brief'              => 'Scary and horror fiction stories',
                'prompt_instruction' => 'Focus on creating fear through atmosphere, psychological tension, disturbing concepts, vulnerability, and escalating dread. Planning should consider the source of fear, how the threat appears, how fear develops, what characters risk losing, and how uncertainty increases tension. Character planning should consider emotional weaknesses, fears, reactions under pressure, personal stakes, and how confronting horror transforms them. Story structure should consider gradual tension building, controlled reveals, moments of terror, confrontation with the threat, and an ending that matches the established horror tone. When combined with other genres, integrate these horror and fear-building elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],
            (object) [
                'name'               => 'Science Fiction',
                'brief'              => 'Science based futuristic fiction',
                'prompt_instruction' => 'Focus on developing a speculative concept based on science, technology, future societies, or alternative possibilities and explore how it changes human life. Planning should consider the rules and limitations of the technology or scientific idea, its impact on society, ethics, politics, economy, and everyday experiences. World planning should consider technological development, social structures, institutions, scientific explanations, and how people adapt to the changed world. Character planning should consider how characters respond to, benefit from, resist, or suffer because of the speculative concept and how it shapes their personal conflicts. Story structure should ensure that the central scientific or technological idea actively drives the conflict, creates meaningful consequences, and contributes to the resolution. When combined with other genres, integrate these speculative and science-based elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'Adventure',
                'brief'              => 'Journey and exploration based stories',
                'prompt_instruction' => 'Focus on creating a meaningful journey involving exploration, discovery, danger, challenges, and personal transformation. Planning should consider the purpose of the journey, destinations, environments, obstacles, resources, cultures encountered, and how each stage challenges the characters. Character planning should consider resourceful protagonists, companion relationships, rivals, enemies, personal goals, and how experiences during the journey change their beliefs and abilities. Story structure should consider escalating challenges, important discoveries, unexpected obstacles, and a climax where the journey leads to a significant personal or external achievement. When combined with other genres, integrate these exploration and journey elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'History',
                'brief'              => 'Historical books and events',
                'prompt_instruction' => 'Focus on presenting real events, their causes, consequences, and the people and systems involved in a clear and evidence-based manner. Planning should consider the historical period, key figures, institutions, geography, political conditions, social structures, causes, sequence of events, and long-term impact. Content planning should consider available sources, competing interpretations, important details, and how information is organized into a meaningful explanation rather than a simple list of events. Structure should create understanding of why events happened, how they developed, and why they continue to matter. When combined with other genres, integrate these historical analysis and evidence-based elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'Psychological',
                'brief'              => 'Stories focused on human mind and emotions',
                'prompt_instruction' => 'Focus on exploring characters’ inner worlds, emotions, thoughts, perceptions, and psychological struggles. Planning should consider motivations, fears, desires, memories, beliefs, emotional patterns, defence mechanisms, and how mental states influence behaviour. Character planning should create psychologically complex individuals whose internal conflicts shape their choices and relationships. Story structure should reveal hidden motivations gradually, explore the difference between appearance and reality, build emotional tension, and resolve deeper internal conflicts. When combined with other genres, integrate these psychological and emotional-depth elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'Supernatural',
                'brief'              => 'Stories involving supernatural elements',
                'prompt_instruction' => 'Focus on developing supernatural forces, beings, or events and exploring how they affect the world and characters. Planning should consider the source of supernatural elements, their rules or mysteries, their consequences, how people perceive them, and how they influence daily life and beliefs. Character planning should consider how characters react to the unknown, their beliefs, fears, curiosity, and how supernatural encounters transform them. Story structure should balance ordinary experiences with extraordinary events, maintain mystery or wonder, and create a resolution consistent with the nature of the supernatural elements. When combined with other genres, integrate these supernatural and otherworldly elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'Crime',
                'brief'              => 'Crime related fiction stories',
                'prompt_instruction' => 'Focus on developing the criminal act, its motives, consequences, investigation, and the people affected by it. Planning should consider the nature of the crime, methods used, evidence, timeline, suspects, motives, investigation process, and social consequences. Character planning should consider criminals with believable psychology, investigators with strengths and limitations, victims, witnesses, and the relationships between those involved. Story structure should consider discovery of the crime, investigation progression, obstacles, pursuit, revelations, and a resolution that addresses both the crime and its consequences. When combined with other genres, integrate these crime and investigation elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'War',
                'brief'              => 'Stories based on wars and conflicts',
                'prompt_instruction' => 'Focus on portraying the causes, experience, consequences, and human impact of war and conflict. Planning should consider the origin of the conflict, opposing sides, military strategies, technology, politics, civilians, social consequences, and the lasting effects of violence. Character planning should consider soldiers, civilians, leaders, and individuals affected by war, including their fears, motivations, moral struggles, losses, and changes caused by conflict. Story structure should balance large-scale events with personal experiences, showing the physical and emotional cost of war while building toward meaningful outcomes. When combined with other genres, integrate these conflict and war-experience elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'Post-Apocalyptic',
                'brief'              => 'Stories after civilization collapse',
                'prompt_instruction' => 'Focus on exploring human survival and rebuilding after a catastrophic event. Planning should consider the cause of collapse, remaining resources, changed environments, new societies, dangers, and survival challenges. Character planning should consider survivors with different backgrounds, skills, beliefs, trauma, hopes, and conflicts about rebuilding the future. Story structure should balance survival challenges with emotional development, discoveries about the past, conflicts between groups, and decisions that shape humanity’s future. When combined with other genres, integrate these survival and rebuilding elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'Western',
                'brief'              => 'Stories set in frontier environments',
                'prompt_instruction' => 'Focus on frontier life, survival, justice, exploration, and conflicts between individuals and changing societies. Planning should consider landscapes, communities, law systems, cultural tensions, resources, and historical conditions. Character planning should consider outlaws, lawmen, settlers, explorers, and individuals seeking freedom or redemption. Story structure should explore survival challenges, moral conflicts, rivalries, journeys, and resolutions shaped by frontier values. When combined with other genres, integrate these western and frontier elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'Mythology',
                'brief'              => 'Stories based on myths, legends, and ancient beliefs',
                'prompt_instruction' => 'Focus on creating narratives inspired by myths, legends, gods, heroes, creatures, and ancient belief systems. World planning should consider mythology, creation stories, divine forces, rituals, cultures, and symbolic meanings. Character planning should consider heroes, gods, monsters, chosen figures, and conflicts between destiny and personal choice. Story structure should explore legendary quests, moral lessons, supernatural challenges, and transformations connected to mythological themes. When combined with other genres, integrate these mythological elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],
        ]);
    }

    public static function languages()
    {
        return collect([
            (object) [
                'name'  => 'English',
                'brief' => 'English',
            ],
        ]);
    }

    public static function aiBrains()
    {
        $configBrains = config('ai-brain.ai-brains', []);

        return collect($configBrains)->map(function ($brain, $key) {

            return (object) [
                'key'               => $key,
                'name'              => $brain['name'] ?? null,
                'model'             => $brain['model'] ?? null,
                'provider'          => $brain['provider'] ?? null,
                'output_type'       => $brain['output_type'] ?? [],
                'api_url'           => $brain['api_url'] ?? null,
                'api_key'           => $brain['api_key'] ?? null,
                'brief'             => $brain['brief'] ?? null,
                'focus'             => $brain['focus'] ?? null,
                'context_window'    => $brain['context_window'] ?? null,
                'average_latency'   => $brain['average_latency'] ?? null,
                'minimum_wait_time' => $brain['minimum_wait_time'] ?? null,
                'timeout_seconds'   => $brain['timeout_seconds'] ?? null,
                'max_output_tokens' => $brain['max_output_tokens'] ?? null,
            ];

        });
    }

    public static function aiPrompts()
    {
        return collect([

            (object) [
                'name'                 => AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                'code'                 => Str::studly(AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION),
                'step_number'          => 1,
                'depend_on_prompt_ids' => null,
                'prompt'               => AiPromptGeneratorHelper::foundationPrompt(),
            ],

            (object) [
                'name'                 => AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER,
                'code'                 => Str::studly(AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER),
                'step_number'          => 2,
                'depend_on_prompt_ids' => null,
                'prompt'               => AiPromptGeneratorHelper::planChapterPrompt(),
            ],

            (object) [
                'name'                 => AiPromptGeneratorHelper::AI_PROMPT_NAME_CHAPTER_CONTENT,
                'code'                 => Str::studly(AiPromptGeneratorHelper::AI_PROMPT_NAME_CHAPTER_CONTENT),
                'step_number'          => 3,
                'depend_on_prompt_ids' => null,
                'prompt'               => AiPromptGeneratorHelper::chapterContentPrompt(),
            ],

        ]);
    }

    public static function novelGeneratorSteps()
    {
        return collect([

            (object) [
                'name'        => AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                'prompt_name' => AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                'depends_on'  => [],
            ],

            (object) [
                'name'        => AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER,
                'prompt_name' => AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER,
                'depends_on'  => [
                    AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                ],
            ],

            (object) [
                'name'        => AiPromptGeneratorHelper::AI_PROMPT_NAME_CHAPTER_CONTENT,
                'prompt_name' => AiPromptGeneratorHelper::AI_PROMPT_NAME_CHAPTER_CONTENT,
                'depends_on'  => [
                    AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                    AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER,
                ],
            ],

        ]);
    }

    public static function aiBrainOutputTypes()
    {
        return collect([
            (object) [
                'name'  => 'Image',
                'code'  => 'Image',
                'brief' => 'AI output type that generates image content.',
            ],

            (object) [
                'name'  => 'Text',
                'code'  => 'Text',
                'brief' => 'AI output type that generates text content.',
            ],
        ]);
    }
}
