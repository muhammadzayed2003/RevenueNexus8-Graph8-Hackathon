<?php

namespace App\Services;

class RevenueKnowledgeService
{
    public function __construct(
        private GeminiEmbeddingService $embeddings,
        private QdrantService $qdrant
    ) {
    }

    public function indexAll(
        ?callable $progress = null
    ): int {
        $this->qdrant->ensureCollection();

        $documents = $this->documents();
        $total = count($documents);
        $count = 0;

        foreach ($documents as $document) {
            $vector = $this->embeddings
                ->embedDocument(
                    $document['text'],
                    $document['title']
                );

            $this->qdrant->upsert([
                [
                    'id' => $this->pointId(
                        $document['key']
                    ),
                    'vector' => $vector,
                    'payload' => $document,
                ],
            ]);

            $count++;

            if ($progress) {
                $progress(
                    $document['title'],
                    $count,
                    $total
                );
            }
        }

        return $count;
    }

    public function retrieve(
        string $question,
        int $userId,
        int $limit = 6
    ): array {
        $vector = $this->embeddings
            ->embedQuery($question);

        return $this->qdrant->search(
            $vector,
            $userId,
            $limit
        );
    }

    private function documents(): array
    {
        return [
            $this->document(
                'platform-overview',
                'RevenueNexus8 Platform Overview',
                <<<'TEXT'
RevenueNexus8 is a revenue decision-intelligence platform built on top of graph8.

Its purpose is to help revenue teams simulate difficult commercial decisions before taking real action. It combines real graph8 CRM context with AI simulations, human approval and controlled graph8 execution.

RevenueNexus8 contains five main experiences:

1. Boardroom8 — buyer committee simulation.
2. Negotiator8 — commercial negotiation war-game.
3. TimeMachine8 — historical revenue strategy replay.
4. Relay8 — human approval and graph8 execution gateway.
5. Evidence8 — product knowledge assistant powered by Qdrant RAG.

The application is built with Laravel 13, PHP 8.4, SQLite, Blade, Tailwind CSS, Alpine.js, Three.js, Gemini APIs and the graph8 REST API.

Authentication is provided through Laravel Breeze. The frontend uses a premium black, white and cream interface with procedural 3D humanoid agents. No external robot image is required.

RevenueNexus8 is a decision-support system. AI simulations are hypothetical and must not be presented as verified buyer actions.
TEXT
            ),

            $this->document(
                'boardroom8',
                'Boardroom8 Buyer Committee Simulation',
                <<<'TEXT'
Boardroom8 simulates a dynamic enterprise buyer committee.

The user selects a company and optionally a deal from synced graph8 CRM data. RevenueNexus8 fetches the relevant company, deal and contact context from graph8.

Gemini analyzes the supplied commercial scenario and generates appropriate committee participants. Real graph8 contacts keep their real names and titles. If a required role has no matching real contact, Boardroom8 creates a role-only simulation persona such as CFO Persona or CTO Persona. It does not invent fictional personal names for missing contacts.

The committee discusses potential value, return on investment, security, integration, implementation, adoption, procurement risk and purchase readiness.

Boardroom8 produces:

- Executive summary
- Purchase probability estimate
- Overall simulation score
- Dynamic buyer personas
- Committee debate
- Key objections
- Recommended next action
- graph8-ready action payload

The debate is an AI simulation based on available evidence. It is not a recording or statement from the real buyer.

The completed recommendation enters Relay8 for human approval.
TEXT
            ),

            $this->document(
                'negotiator8',
                'Negotiator8 Commercial War-Game',
                <<<'TEXT'
Negotiator8 runs a controlled multi-round negotiation war-game between a simulated buyer agent and seller agent.

The user may select a real synced graph8 deal or run a standalone scenario. The user supplies verified product details, commercial terms, the proposed price, minimum acceptable price, authorised concessions, non-negotiable conditions, buyer objection, primary objective and number of rounds.

The buyer agent applies commercial pressure and challenges price, risk and contract terms. The seller agent protects deal value and may only use concessions explicitly authorised by the user.

Safety rules include:

- No recommended or offered price may fall below the minimum acceptable price.
- A minimum price does not automatically authorise a discount.
- The seller may only use exact authorised concessions.
- Missing product facts remain unknown.
- Simulated buyer responses cannot be treated as real buyer acceptance.
- Final actions require human review.

Negotiator8 produces:

- Win probability estimate
- Overall score
- Recommended price
- Recommended move
- Multi-round buyer and seller dialogue
- Pressure and confidence percentages
- Key risks
- Acceptable concessions
- Relay8 recommendation

The generated dialogue can be played through device speech synthesis. The interface displays animated 3D agents while the conversation is spoken.
TEXT
            ),

            $this->document(
                'timemachine8',
                'TimeMachine8 Historical Strategy Replay',
                <<<'TEXT'
TimeMachine8 evaluates how an alternative revenue strategy might have changed a historical deal path.

It uses available graph8 webhook events and live deal snapshots stored by RevenueNexus8. Events are arranged chronologically and supplied to Gemini together with a candidate strategy.

For every available event, TimeMachine8 compares:

- What actually occurred in the stored evidence
- How the candidate strategy would respond
- The likely commercial impact of the alternative action
- The actual path and simulated path

TimeMachine8 produces:

- Executive summary
- Actual outcome
- Simulated outcome
- Projected uplift estimate
- Confidence score
- Chronological replay timeline
- Key findings
- Evidence limitations
- Relay8 recommendation

A deal snapshot is not the same as complete CRM history. TimeMachine8 explicitly reports limited evidence and must not claim statistical certainty from a small sample.

Its output is a strategy simulation, not proof that the alternative result would definitely occur.
TEXT
            ),

            $this->document(
                'relay8',
                'Relay8 Approval and graph8 Execution',
                <<<'TEXT'
Relay8 is the human approval and execution gateway of RevenueNexus8.

Recommendations created by Boardroom8, Negotiator8 and TimeMachine8 enter a pending approval queue.

A user can:

- Review the source simulation
- Inspect the recommendation
- Inspect the proposed action type
- Inspect the dynamic payload
- Approve the recommendation
- Reject the recommendation
- Execute an approved recommendation through graph8

Only approved recommendations can be executed.

The current graph8 execution creates a real note and follow-up task linked to the relevant graph8 deal when valid graph8 identifiers are available. Execution responses are stored for traceability.

Relay8 preserves:

- Recommendation ID
- Simulation ID
- Source module
- Approving user
- Approval timestamp
- graph8 company, contact and deal identifiers
- graph8 API response

Relay8 does not treat an AI recommendation as permission to contact a buyer or make an irreversible commercial commitment. Human approval remains mandatory.

A graph8 agent named Relay8 Coordinator can receive approved RevenueNexus8 reports, distinguish verified facts from simulations, identify missing evidence and recommend the safest next step.
TEXT
            ),

            $this->document(
                'evidence8',
                'Evidence8 Qdrant RAG Assistant',
                <<<'TEXT'
Evidence8 is the product knowledge assistant embedded across RevenueNexus8.

It appears as a floating circular agent-face button on authenticated pages. The button uses the same procedural 3D visual language as the rest of the RevenueNexus8 interface and includes a subtle breathing animation.

When opened, Evidence8 provides a conversational panel where users can ask how RevenueNexus8 works.

Evidence8 is built with:

- Laravel chat endpoint
- Gemini embedding model
- 768-dimensional text embeddings
- Qdrant vector database
- Semantic retrieval
- Gemini grounded answer generation
- Alpine.js floating chat interface

Evidence8 contains only approved RevenueNexus8 product documentation. It does not store graph8 deals, companies, contacts, events, simulation results or recommendation records in Qdrant.

For every question:

1. Gemini creates a query embedding.
2. Qdrant retrieves relevant RevenueNexus8 documentation.
3. The answer model receives only the retrieved documentation.
4. The assistant answers from that evidence.
5. Relevant source titles can be displayed in the chat.

If the documentation does not contain the answer, Evidence8 must clearly say that the information is not available instead of inventing it.
TEXT
            ),
        ];
    }

    private function document(
        string $key,
        string $title,
        string $text
    ): array {
        return [
            'key' => $key,
            'type' => 'product_documentation',
            'title' => $title,
            'text' => $text,
            'visibility' => 'workspace',
            'user_id' => null,
            'graph8_id' => null,
            'indexed_at' => now()
                ->toIso8601String(),
        ];
    }

    private function pointId(string $key): string
    {
        $hash = md5($key);

        return substr($hash, 0, 8)
            .'-'.substr($hash, 8, 4)
            .'-'.substr($hash, 12, 4)
            .'-'.substr($hash, 16, 4)
            .'-'.substr($hash, 20, 12);
    }
}