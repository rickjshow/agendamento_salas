"use client";

import React, { useEffect, useState } from "react";
import Layout from "../components/Layout";
import axios from "axios";
import { format } from "date-fns";
import { ptBR } from "date-fns/locale";



interface HistoricoReserva {
    id: number;
    reserva_id: number;
    alteracoes: string;
    modificado_em: string;
}

const HistoricoReservas = () => {
    const [historicos, setHistoricos] = useState<HistoricoReserva[]>([]);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const fetchHistoricos = async () => {
            try {
                console.log("Fetching /api/historico-reservas...");
                const response = await axios.get("http://127.0.0.1:8000/api/historico-reservas");
                console.log("Response Data:", response.data);
                setHistoricos(response.data); // Preenche o estado com os dados recebidos
            } catch (err) {
                console.error("API Error:", err);
                setError("Erro ao carregar o histórico de reservas.");
            }
        };

        fetchHistoricos();
    }, []); // Executa a requisição apenas ao montar o componente

    if (error) {
        return (
            <Layout>
                <p className="text-red-500">{error}</p>
            </Layout>
        );
    }

    return (
        <Layout>
            <div className="container mx-auto p-4">
                <h1 className="text-2xl font-bold mb-4">Histórico de Reservas</h1>
                {historicos.length === 0 ? (
                    <p>Nenhum histórico encontrado.</p>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="min-w-full bg-white border border-gray-200 rounded-lg">
                            <thead>
                                <tr className="bg-gray-100 border-b">
                                    <th className="text-left px-4 py-2">Reserva ID</th>
                                    <th className="text-left px-4 py-2">Alterações</th>
                                    <th className="text-left px-4 py-2">Modificado em</th>
                                </tr>
                            </thead>
                            <tbody>
                                {historicos.map((historico) => (
                                    <tr
                                        key={historico.id}
                                        className="border-b hover:bg-gray-50"
                                    >
                                        <td className="px-4 py-2">{historico.reserva_id}</td>
                                        <td className="px-4 py-2">{historico.alteracoes}</td>
                                        <td className="px-4 py-2">
                                            {format(new Date(historico.modificado_em), "dd/MM/yyyy HH:mm", {
                                                locale: ptBR,
                                            })}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </Layout>
    );
};

export default HistoricoReservas;
