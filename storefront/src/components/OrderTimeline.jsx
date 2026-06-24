import React from 'react';
import { motion } from 'framer-motion';

const steps = [
  { key: 'pending', label: 'Recebido', icon: '📥' },
  { key: 'confirmed', label: 'Confirmado', icon: '✓' },
  { key: 'processing', label: 'Em Preparação', icon: '⚙️' },
  { key: 'delivered', label: 'Entregue', icon: '📦' },
];

export default function OrderTimeline({ status }) {
  const statusOrder = { 0: 0, 1: 1, 2: 2, 3: 2, 4: 3, 5: -1 };
  const current = statusOrder[status] ?? -1;
  const isCancelled = status === 5;

  if (isCancelled) {
    return (
      <div className="flex items-center justify-center py-8">
        <div className="text-center">
          <span className="text-4xl">❌</span>
          <p className="text-red-600 font-medium mt-2">Pedido Cancelado</p>
        </div>
      </div>
    );
  }

  return (
    <div className="flex items-center justify-between py-6">
      {steps.map((step, i) => {
        const done = i <= current;
        const currentStep = i === current;
        return (
          <React.Fragment key={step.key}>
            <div className="flex flex-col items-center">
              <motion.div initial={{ scale: 0 }} animate={{ scale: 1 }} transition={{ delay: i * 0.15, type: 'spring' }}
                className={`w-12 h-12 rounded-full flex items-center justify-center text-lg font-bold shadow-sm
                  ${done ? 'bg-blue-500 text-white shadow-blue-200' : 'bg-gray-100 text-gray-400'}`}>
                {done ? (step.key === 'pending' ? '📥' : '✓') : i + 1}
              </motion.div>
              <span className={`text-xs mt-2 font-medium ${done ? 'text-blue-600' : 'text-gray-400'}`}>{step.label}</span>
            </div>
            {i < steps.length - 1 && (
              <div className={`flex-1 h-1 mx-2 rounded-full ${done ? 'bg-blue-500' : 'bg-gray-200'}`} />
            )}
          </React.Fragment>
        );
      })}
    </div>
  );
}
