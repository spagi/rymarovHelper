import { openai } from "@ai-sdk/openai"
import { streamText } from "ai"

// Allow streaming responses up to 30 seconds
export const maxDuration = 30

export async function POST(req: Request) {
  const { messages } = await req.json()

  const result = streamText({
    model: openai("gpt-4o"),
    system:
      "You are a helpful AI assistant for the city of Rýmařov in the Czech Republic. You help citizens and visitors with information about city services, local events, transportation, tourist attractions, municipal offices, and general questions about Rýmařov. You should respond in Czech language and be knowledgeable about local Czech municipal services and procedures. Always be polite, informative, and helpful.",
    messages,
  })

  return result.toDataStreamResponse()
}
