<?php
namespace App\Helpers;

class SeederHelper
{
    public static function genres()
    {
        return collect([
            (object) [
                'name'               => 'Fantasy',
                'brief'              => 'Fantasy fiction with imaginary worlds and magic',
                'prompt_instruction' => 'Focus on building an invented world with consistent internal rules, a defined magic system with limitations and costs, memorable creatures and cultures, rich mythology, and conflicts that naturally emerge from the setting. World planning should consider geography, societies, power structures, laws of the world, magic sources and their consequences, technology level, mythology, and historical events that shape present tensions. Character planning should consider how characters are influenced by this world, how abilities and limitations create moral and practical challenges, and how personal goals connect with larger world conflicts. Story structure should ensure that fantastical elements escalate logically, stakes grow naturally, and the resolution remains consistent with the established world rules. When combined with other genres, integrate these fantasy world-building elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'Dark Fantasy',
                'brief'              => 'Dark and mysterious fantasy stories',
                'prompt_instruction' => 'Focus on creating a grim and morally complex world where supernatural forces, magic, corruption, and horror exist together. Planning should consider the cost of power, the consequences of using forbidden forces, oppressive environments, decaying societies, supernatural threats, and how the world itself creates conflict. Character planning should consider morally complex protagonists, flawed heroes, understandable but dangerous antagonists, internal struggles, corruption, sacrifice, and the personal cost of survival. Story structure should consider escalating darkness, meaningful suffering, difficult choices, fragile hope, and resolutions where victory carries emotional or moral consequences. When combined with other genres, integrate these dark fantasy and moral-conflict elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'Historical Fiction',
                'brief'              => 'Stories based on historical events and periods',
                'prompt_instruction' => 'Focus on creating an authentic historical setting where real social conditions, events, and cultural realities influence the characters and conflict. Setting planning should consider the time period, geography, politics, class structures, customs, technology, economy, and historical events that shape the world. Character planning should consider believable behaviour within the chosen era, limitations created by society, personal motivations, and how historical circumstances influence individual choices. Story structure should consider how larger historical events intersect with personal experiences, how historical changes create tension, and how the conclusion remains consistent with the period while delivering emotional impact. When combined with other genres, integrate these historical authenticity elements with the requirements of the additional genres while avoiding duplicate planning elements.',
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
                'name'               => 'Biography',
                'brief'              => 'Life stories of real people',
                'prompt_instruction' => 'Focus on presenting the life of a real person through important experiences, achievements, struggles, relationships, and lasting influence. Planning should consider the subject’s background, historical context, defining moments, personal qualities, challenges, successes, failures, and impact on others. Character planning should consider the subject’s motivations, decisions, beliefs, relationships, and how external circumstances shaped their life. Story structure should create a meaningful life narrative rather than a simple timeline by highlighting turning points, major transformations, and significant periods. The narrative should prioritize factual accuracy, context, depth, and a balanced understanding of the subject. When combined with other genres, integrate these life-story and subject-development elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'Autobiography',
                'brief'              => 'Life story written by the person themselves',
                'prompt_instruction' => 'Focus on creating a personal first-person reflection where the individual explores their experiences, choices, growth, struggles, and understanding of their own life. Planning should consider personal voice, memory, important life stages, achievements, regrets, relationships, values, and moments that shaped identity. Character planning should consider how the narrator views themselves, how their perspective changes over time, and how personal experiences influence their decisions. Story structure should create a clear emotional journey showing transformation, self-discovery, challenges faced, and lessons learned. The narrative should prioritize honesty, self-awareness, reflection, and a meaningful understanding of the person behind the events. When combined with other genres, integrate these autobiographical and self-reflection elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'History',
                'brief'              => 'Historical books and events',
                'prompt_instruction' => 'Focus on presenting real events, their causes, consequences, and the people and systems involved in a clear and evidence-based manner. Planning should consider the historical period, key figures, institutions, geography, political conditions, social structures, causes, sequence of events, and long-term impact. Content planning should consider available sources, competing interpretations, important details, and how information is organized into a meaningful explanation rather than a simple list of events. Structure should create understanding of why events happened, how they developed, and why they continue to matter. When combined with other genres, integrate these historical analysis and evidence-based elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'Philosophy',
                'brief'              => 'Books about ideas and philosophy',
                'prompt_instruction' => 'Focus on exploring central ideas, questions, arguments, and concepts through logical reasoning and meaningful analysis. Planning should consider the main philosophical question, key concepts, assumptions, arguments, counterarguments, examples, and practical implications of the ideas. Content planning should prioritize clarity, structured reasoning, understandable explanations, thought experiments, and connections between abstract ideas and human experience. Structure should develop ideas progressively, address opposing views, and guide readers toward deeper understanding. When combined with other genres, integrate these philosophical and conceptual elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'Self Help',
                'brief'              => 'Personal development and improvement books',
                'prompt_instruction' => 'Focus on providing practical guidance that helps readers understand a problem, develop useful habits, and achieve personal improvement. Planning should consider the target audience, their challenges, goals, obstacles, and realistic methods for progress. Content planning should include clear principles, actionable steps, examples, exercises, ways to measure improvement, and strategies for overcoming difficulties. Structure should move logically from understanding the problem to applying solutions while maintaining clarity and practical value. When combined with other genres, integrate these personal-development and practical-guidance elements with the requirements of the additional genres while avoiding duplicate planning elements.',
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
                'name'               => 'Political Fiction',
                'brief'              => 'Stories involving politics and society',
                'prompt_instruction' => 'Focus on exploring power structures, political systems, ideology, institutions, and how social forces influence individuals and communities. Planning should consider the government structure, political factions, laws, economy, media, public opinion, conflicts of interest, and struggles for influence or control. Character planning should consider politicians, leaders, activists, officials, and ordinary citizens with different beliefs, ambitions, compromises, and personal consequences caused by political decisions. Story structure should consider strategic conflicts, alliances, betrayals, public and private struggles, rising political tension, and a resolution that reveals the impact of the power struggle. When combined with other genres, integrate these political and power-dynamics elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'War',
                'brief'              => 'Stories based on wars and conflicts',
                'prompt_instruction' => 'Focus on portraying the causes, experience, consequences, and human impact of war and conflict. Planning should consider the origin of the conflict, opposing sides, military strategies, technology, politics, civilians, social consequences, and the lasting effects of violence. Character planning should consider soldiers, civilians, leaders, and individuals affected by war, including their fears, motivations, moral struggles, losses, and changes caused by conflict. Story structure should balance large-scale events with personal experiences, showing the physical and emotional cost of war while building toward meaningful outcomes. When combined with other genres, integrate these conflict and war-experience elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'Young Adult',
                'brief'              => 'Stories for young adult readers',
                'prompt_instruction' => 'Focus on exploring identity, personal growth, relationships, independence, and challenges connected to adolescence or early adulthood. Planning should consider the characters’ stage of life, personal struggles, social pressures, friendships, family relationships, and questions of belonging and self-discovery. Character planning should create relatable protagonists with authentic emotions, weaknesses, dreams, and evolving understanding of themselves and the world. Story structure should consider meaningful challenges, emotional growth, consequences of choices, and a resolution that reflects earned maturity and personal development. When combined with other genres, integrate these coming-of-age and identity-development elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'Children',
                'brief'              => 'Books for children',
                'prompt_instruction' => 'Focus on creating age-appropriate stories with clear language, engaging characters, imagination, and meaningful emotional experiences for young readers. Planning should consider the target age group, reading ability, suitable themes, simple conflicts, curiosity, learning opportunities, and emotional understanding. Character planning should consider memorable child-friendly characters, relatable experiences, imagination, friendships, and positive growth. Story structure should maintain an engaging pace, understandable challenges, creative situations, and a satisfying resolution that provides emotional value without becoming overly complex. When combined with other genres, integrate these child-focused and age-appropriate elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'Poetry',
                'brief'              => 'Poetry and verses',
                'prompt_instruction' => 'Focus on expressing emotions, ideas, experiences, and imagery through carefully chosen language, rhythm, structure, and poetic techniques. Planning should consider the central theme, emotional purpose, tone, imagery, symbolism, metaphor, sound, rhythm, and the poetic form best suited to the subject. Content planning should prioritize meaningful word choices, emotional depth, sensory experiences, and the relationship between form and meaning. Structure should consider line arrangement, stanza development, pacing, and how each element contributes to the overall poetic experience. When combined with other genres, integrate these poetic and expressive-language elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],

            (object) [
                'name'               => 'Short Story',
                'brief'              => 'Short fiction stories',
                'prompt_instruction' => 'Focus on creating a concentrated narrative built around a strong central idea, limited scope, meaningful characters, and an impactful moment or transformation. Planning should consider the core premise, central conflict, essential characters, important details, and how every element contributes to the intended effect. Character planning should focus on creating depth quickly, showing meaningful motivations, and revealing change through limited but significant events. Story structure should consider efficient pacing, entering the story at the most important moment, developing tension within a limited space, and creating an ending that leaves a lasting emotional or intellectual impact. When combined with other genres, integrate these focused storytelling and economical narrative elements with the requirements of the additional genres while avoiding duplicate planning elements.',
            ],
        ]);
    }
    public static function aiPrompts()
    {
        return collect([

            (object) [
                'name'                 => "Novel Generation Planning",
                'step'                 => 1,
                'depend_on_prompt_ids' => null,
                'prompt'               => <<<'PROMPT'

You are a professional novel generation planning AI.

Your task is not to write the novel.

Create a professional human-level novel generation blueprint.

Think like an experienced novelist planning a real novel.

User Input:

Genre:
{{genres}}

Main Character Gender:
{{main_character_gender}}

Is 18+:
{{is_18_plus}}

Enable Mature Content:
{{enable_mature_content}}

Language:
{{language}}

Novel Continuity:
{{novel_continuity}}

Additional Novel Information:
{{additional_information}}


Genre Instructions:

{{genre_instructions}}


Important instructions for Genre Instructions:

- Genre Instructions are provided as a complete merged instruction text.
- Read and understand the entire instruction carefully.
- Multiple genre requirements may exist.
- Identify all required creative elements from the instruction.
- If similar requirements appear multiple times, merge them.
- Avoid duplicate planning sections.
- Create a clean combined structure.
- Do not ignore any important genre specific requirement.


Create planning for:

- Genre structure requirements
- Character requirements
- Relationship requirements
- Conflict requirements
- Story structure requirements
- Continuity requirements
- Language requirements


Output JSON format:

{
    "genre_structure_elements": [],
    "character_requirements": [],
    "relationship_requirements": [],
    "conflict_requirements": [],
    "story_structure_requirements": [],
    "continuity_requirements": [],
    "language_requirements": [],
    "generation_notes": []
}

PROMPT
            ],

            (object) [
                'name'                 => "Story Foundation Development",
                'step'                 => 2,
                'depend_on_prompt_ids' => [1],
                'prompt'               => <<<'PROMPT'

Use previous planning output:

{{prompt_1_output}}


Create the complete story foundation.

Think like a professional human novelist.

Develop:

- Core story concept
- Main theme
- Narrative direction
- Central conflict
- Emotional foundation
- Overall storytelling approach


Output JSON format:

{
    "story_concept": "",
    "main_theme": "",
    "narrative_direction": "",
    "central_conflict": "",
    "emotional_foundation": "",
    "storytelling_notes": []
}

PROMPT
            ],

            (object) [
                'name'                 => "Character Development Planning",
                'step'                 => 3,
                'depend_on_prompt_ids' => [1, 2],
                'prompt'               => <<<'PROMPT'

Use previous outputs:

Planning Output:
{{prompt_1_output}}

Story Foundation:
{{prompt_2_output}}


Create detailed character planning.

Develop:

- Main character background
- Personality
- Goals
- Motivation
- Strengths
- Weaknesses
- Internal conflict
- External conflict
- Supporting characters
- Relationships
- Character growth arc


Output JSON format:

{
    "main_character": {},
    "supporting_characters": [],
    "relationships": [],
    "character_goals": [],
    "character_conflicts": [],
    "character_growth_arc": ""
}

PROMPT
            ],

            (object) [
                'name'                 => "Story Plot Generation",
                'step'                 => 4,
                'depend_on_prompt_ids' => [2, 3],
                'prompt'               => <<<'PROMPT'

Use previous outputs:

Story Foundation:
{{prompt_2_output}}

Character Planning:
{{prompt_3_output}}


Create the complete novel plot.

At this stage decide:

- Novel Title
- Novel Subtitle
- Complete story direction
- Beginning
- Middle progression
- Major turning points
- Climax
- Ending resolution


The title and subtitle should be created after understanding the complete story concept.

Think like a professional novelist creating a publishable story.


Output JSON format:

{
    "novel_title": "",
    "novel_subtitle": "",
    "plot_summary": "",
    "beginning": "",
    "middle": "",
    "turning_points": [],
    "climax": "",
    "ending_resolution": ""
}

PROMPT
            ],

            (object) [
                'name'                 => "Chapter Planning",
                'step'                 => 5,
                'depend_on_prompt_ids' => [4],
                'prompt'               => <<<'PROMPT'

Use story plot output:

{{prompt_4_output}}


Create complete chapter planning.

Determine:

- Total number of chapters
- Chapter sequence
- Chapter name
- Chapter title
- Chapter purpose
- Important events
- Character progression
- Story progression


Every chapter must have:

- chapter_name
- chapter_title


Output JSON format:

{
    "total_chapters": 0,
    "chapters": [
        {
            "chapter_number": 1,
            "chapter_name": "",
            "chapter_title": "",
            "purpose": "",
            "events": []
        }
    ]
}

PROMPT
            ],

            (object) [
                'name'                 => "Chapter Writing",
                'step'                 => 6,
                'depend_on_prompt_ids' => [5],
                'prompt'               => <<<'PROMPT'

Use chapter planning output:

{{prompt_5_output}}


Write only the requested chapter.

Rules:

- Generate one chapter at a time.
- Do not generate the complete novel together.
- Follow chapter planning.
- Maintain previous chapter continuity.
- Maintain character consistency.
- Maintain story tone.
- Write like a professional human novelist.


Current Chapter Information:

{{chapter_information}}


Output JSON format:

{
    "chapter_number": 0,
    "chapter_title": "",
    "chapter_content": ""
}

PROMPT
            ],

            (object) [
                'name'                 => "Novel Quality Review",
                'step'                 => 7,
                'depend_on_prompt_ids' => [6],
                'prompt'               => <<<'PROMPT'

Review the complete generated novel.

Generated Chapters:

{{prompt_6_output}}


Analyze the novel like a professional editor.

Review:

- Overall story quality
- Plot structure
- Character consistency
- Character development
- Timeline consistency
- World consistency
- Emotional impact
- Reader engagement
- Writing quality
- Continuity problems
- Improvement opportunities


Output JSON format:

{
    "overall_quality_score": 0,
    "story_review": "",
    "character_review": "",
    "plot_review": "",
    "continuity_review": "",
    "writing_quality_review": "",
    "identified_issues": [],
    "improvement_suggestions": []
}

PROMPT
            ],

        ]);
    }
}
