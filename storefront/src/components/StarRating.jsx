import React, { useState } from 'react';
import { motion } from 'framer-motion';

export default function StarRating({ value = 0, onChange, size = 'text-3xl', readonly = false }) {
  const [hovered, setHovered] = useState(0);
  const display = readonly ? value : (hovered || value);

  return (
    <div className="flex gap-1">
      {[1, 2, 3, 4, 5].map(i => (
        <motion.button key={i} type="button" disabled={readonly}
          whileHover={readonly ? {} : { scale: 1.2 }}
          whileTap={readonly ? {} : { scale: 0.9 }}
          onMouseEnter={() => !readonly && setHovered(i)}
          onMouseLeave={() => !readonly && setHovered(0)}
          onClick={() => onChange?.(i)}
          className={`${size} ${i <= display ? 'text-yellow-400' : 'text-gray-200'} ${readonly ? '' : 'cursor-pointer'} transition-colors duration-150`}>
          ★
        </motion.button>
      ))}
    </div>
  );
}
