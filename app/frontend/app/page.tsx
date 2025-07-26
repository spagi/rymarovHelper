"use client"

// KROK 1: Odstranili jsme import 'useChat'. Místo něj použijeme 'useState'.
import { useState } from 'react'
import { Send, Bot } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"

// Definujeme si typ pro jednu zprávu, abychom měli pořádek
type Message = {
  id: number;
  role: 'user' | 'assistant';
  content: string;
};

export default function CityHelperChat() {
  // KROK 2: Místo useChat definujeme vlastní stavy pomocí useState
  const [messages, setMessages] = useState<Message[]>([]);
  const [input, setInput] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  // KROK 3: Toto je naše vlastní handleSubmit funkce. NENÍ z useChat.
  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    if (!input.trim() || isLoading) return;

    const userMessage: Message = {
      id: Date.now(),
      content: input,
      role: 'user',
    };

    setMessages((prev) => [...prev, userMessage]);
    const currentInput = input;
    setInput('');
    setIsLoading(true);

    try {
      // Odesíláme PŘESNĚ ten formát, který naše API chce
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/ask`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        // TOTO JE KLÍČOVÉ: Posíláme jednoduchý JSON, ne ten složitý
        body: JSON.stringify({ question: currentInput }),
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.detail || `API error: ${response.statusText}`);
      }

      const data = await response.json();

      const aiMessage: Message = {
        id: Date.now() + 1,
        content: data.answer || 'Nastala chyba, nedostal jsem platnou odpověď.',
        role: 'assistant',
      };

      setMessages((prev) => [...prev, aiMessage]);

    } catch (error) {
      console.error("Failed to fetch AI response:", error);
      const errorMessage: Message = {
        id: Date.now() + 1,
        content: error instanceof Error ? error.message : 'Omlouvám se, došlo k chybě při komunikaci se serverem.',
        role: 'assistant',
      };
      setMessages((prev) => [...prev, errorMessage]);
    } finally {
      setIsLoading(false);
    }
  };

  // Vizuální část (JSX)
  return (
      <div className="min-h-screen bg-slate-100 py-8 px-4">
        <div className="max-w-2xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
          {/* Header */}
          <div className="bg-white border-b border-slate-200 p-4">
            <div className="flex items-center gap-3">
              <div className="w-12 h-8 flex items-center justify-center">
                <img
                    src="/images/logo.jpg" // Cesta k souboru v 'public' adresáři
                    alt="Město Rýmařov"
                    className="h-8 w-auto object-contain"
                />
              </div>
              <div>
                <h1 className="text-lg font-semibold text-slate-900">Rýmařovský asistent</h1>
                <p className="text-sm text-slate-600">Neoficiální chatbot města Rýmařov</p>
              </div>
            </div>
          </div>

          {/* Conversation Area */}
          <div className="h-96 overflow-y-auto p-4 space-y-4">
            {messages.length === 0 && (
                <div className="text-center text-slate-500 mt-8">
                  <Bot className="w-12 h-12 mx-auto mb-4 text-slate-400" />
                  <p className="text-lg font-medium mb-2">Vítejte u asistenta města Rýmařov!</p>
                  <p className="text-sm">
                    Zeptejte se mě na cokoliv ohledně úřední desky města Rýmařov.
                  </p>
                </div>
            )}

            {messages.map((message) => (
                <div key={message.id} className={`flex ${message.role === "user" ? "justify-end" : "justify-start"} gap-3`}>
                  {message.role === "assistant" && (
                      <div className="w-8 h-8 bg-slate-200 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                        <Bot className="w-4 h-4 text-slate-600" />
                      </div>
                  )}

                  <div
                      className={`max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${
                          message.role === "user"
                              ? "bg-blue-500 text-white rounded-br-sm"
                              : "bg-slate-200 text-slate-900 rounded-bl-sm"
                      }`}
                  >
                    <p className="text-sm leading-relaxed whitespace-pre-wrap">
                      {message.content}
                    </p>
                  </div>
                </div>
            ))}

            {isLoading && (
                <div className="flex justify-start gap-3">
                  <div className="w-8 h-8 bg-slate-200 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                    <Bot className="w-4 h-4 text-slate-600" />
                  </div>
                  <div className="bg-slate-200 text-slate-900 rounded-lg rounded-bl-sm px-4 py-2">
                    <div className="flex space-x-1">
                      <div className="w-2 h-2 bg-slate-400 rounded-full animate-bounce"></div>
                      <div className="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style={{ animationDelay: "0.1s" }} ></div>
                      <div className="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style={{ animationDelay: "0.2s" }} ></div>
                    </div>
                  </div>
                </div>
            )}
          </div>

          {/* Input Form */}
          <div className="border-t border-slate-200 p-4">
            <form onSubmit={handleSubmit} className="flex gap-2">
              <Input
                  value={input}
                  // KROK 4: Změna handleru pro input
                  onChange={(e) => setInput(e.target.value)}
                  placeholder="Položte mi dotaz..."
                  className="flex-1 border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                  disabled={isLoading}
              />
              <Button
                  type="submit"
                  size="icon"
                  className="bg-blue-500 hover:bg-blue-600 text-white flex-shrink-0"
                  disabled={isLoading || !input.trim()}
              >
                <Send className="w-4 h-4" />
                <span className="sr-only">Odeslat zprávu</span>
              </Button>
            </form>
          </div>
        </div>
      </div>
  )
}