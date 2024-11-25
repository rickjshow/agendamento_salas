"use client";

import React, { useEffect, useState } from "react";
import Layout from "../components/Layout";
import axios from "axios";
import withAuth from "../Hoc/withAuth";

interface ReservaPassada {
    id: number;
    ambiente: {
        nome: string;
    };
    usuario: {
        nome: string;
    };
    hora_inicio: string; // Adicione essa linha
    hora_fim: string;
}


const ReservasPassadas = () => {
    const [reservas, setReservas] = useState<ReservaPassada[]>([]);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const fetchReservasPassadas = async () => {
            try {
                // Obtém o usuário do localStorage
                const user = localStorage.getItem("user");
                if (!user) {
                    setError("Usuário não encontrado no localStorage.");
                    return;
                }

                const { id } = JSON.parse(user);

                console.log("Fetching /api/reservaspassadas...");
                const response = await axios.get(
                    `http://127.0.0.1:8000/api/reservaspassadas?usuario_id=${id}`
                );

                console.log("Response Data:", response.data);
                setReservas(response.data); // Preenche o estado com os dados recebidos
            } catch (err) {
                console.error("API Error:", err);
                setError("Erro ao carregar as reservas passadas.");
            }
        };

        fetchReservasPassadas();
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
                <h1 className="text-2xl font-bold mb-4">Reservas Passadas</h1>
                {reservas.length === 0 ? (
                    <p>Nenhuma reserva passada encontrada.</p>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="min-w-full bg-white border border-gray-200 rounded-lg">
                            <thead>
                                <tr className="bg-gray-100 border-b">
                                    <th className="text-left px-4 py-2">Ambiente</th>
                                    <th className="text-left px-4 py-2">Usuário</th>
                                    <th className="text-left px-4 py-2">Hora Inicio</th>
                                    <th className="text-left px-4 py-2">Hora Fim</th>
                                </tr>
                            </thead>
                            <tbody>
                                {reservas.map((reserva) => (
                                    <tr
                                        key={reserva.id}
                                        className="border-b hover:bg-gray-50"
                                    >
                                        <td className="px-4 py-2">{reserva.ambiente?.nome || "N/A"}</td>
                                        <td className="px-4 py-2">{reserva.usuario?.nome || "N/A"}</td>
                                        <td className="px-4 py-2">{reserva.hora_inicio}</td>
                                        <td className="px-4 py-2">{reserva.hora_fim}</td>
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

export default withAuth(ReservasPassadas);
