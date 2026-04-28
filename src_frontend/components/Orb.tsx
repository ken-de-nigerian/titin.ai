import { motion } from "framer-motion";
import { cn } from "@/lib/utils";

export type OrbState = "idle" | "listening" | "thinking" | "speaking";

interface OrbProps {
  state?: OrbState;
  size?: number;
  className?: string;
}

const stateConfig: Record<OrbState, { scale: [number, number, number]; duration: number; ringOpacity: number }> = {
  idle:      { scale: [1, 1.012, 1], duration: 6.0, ringOpacity: 0.5 },
  listening: { scale: [1, 1.04, 1],  duration: 1.8, ringOpacity: 0.9 },
  thinking:  { scale: [1, 1.008, 1], duration: 3.2, ringOpacity: 0.45 },
  speaking:  { scale: [1, 1.025, 1], duration: 1.1, ringOpacity: 1.0 },
};

/**
 * AI presence — soft sphere with a hint of brand (indigo) for a warmer SaaS feel.
 */
export function Orb({ state = "idle", size = 320, className }: OrbProps) {
  const cfg = stateConfig[state];

  return (
    <div
      className={cn("relative flex items-center justify-center", className)}
      style={{ width: size, height: size }}
    >
      {/* outer soft halo */}
      <div
        className="absolute rounded-full"
        style={{
          inset: "-6%",
          background: "radial-gradient(circle, oklch(0.55 0.20 268 / 0.10), transparent 65%)",
          filter: "blur(28px)",
          opacity: cfg.ringOpacity,
        }}
      />
      {/* concentric rings */}
      <div
        className="absolute inset-0 rounded-full border"
        style={{ borderColor: `oklch(0.55 0.20 268 / ${0.08 + cfg.ringOpacity * 0.06})` }}
      />
      <div
        className="absolute rounded-full border"
        style={{ inset: "9%", borderColor: `oklch(0.20 0.02 265 / ${0.05 + cfg.ringOpacity * 0.05})` }}
      />

      {/* ambient shadow */}
      <div
        className="absolute rounded-full"
        style={{
          width: "82%",
          height: "82%",
          top: "12%",
          background: "radial-gradient(circle, oklch(0.20 0.02 265 / 0.18), transparent 70%)",
          filter: "blur(34px)",
        }}
      />

      {/* core sphere */}
      <motion.div
        className="relative rounded-full"
        style={{
          width: "70%",
          height: "70%",
          background:
            "radial-gradient(circle at 32% 26%, oklch(0.72 0.10 268) 0%, oklch(0.42 0.14 268) 48%, oklch(0.22 0.08 268) 100%)",
          boxShadow:
            "inset 0 -32px 60px oklch(0.10 0.05 268 / 0.55), inset 0 22px 40px oklch(1 0 0 / 0.18), 0 18px 40px -10px oklch(0.42 0.14 268 / 0.35)",
        }}
        animate={{ scale: cfg.scale }}
        transition={{ duration: cfg.duration, repeat: Infinity, ease: "easeInOut" }}
      >
        {/* highlight */}
        <div
          className="absolute rounded-full"
          style={{
            top: "9%", left: "20%", width: "42%", height: "28%",
            background: "radial-gradient(ellipse, oklch(1 0 0 / 0.32), transparent 70%)",
            filter: "blur(10px)",
          }}
        />
        {/* equator */}
        <div className="absolute left-0 right-0 top-1/2 h-px" style={{ background: "oklch(1 0 0 / 0.08)" }} />
      </motion.div>
    </div>
  );
}
