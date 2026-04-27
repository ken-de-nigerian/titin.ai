import { motion } from "framer-motion";
import { cn } from "@/lib/utils";

export const ORB_STATES = ["idle", "listening", "thinking", "speaking"];

const stateConfig = {
  idle: { scale: [1, 1.012, 1], duration: 6.0, ringOpacity: 0.55 },
  listening: { scale: [1, 1.035, 1], duration: 1.8, ringOpacity: 0.85 },
  thinking: { scale: [1, 1.008, 1], duration: 3.2, ringOpacity: 0.45 },
  speaking: { scale: [1, 1.025, 1], duration: 1.1, ringOpacity: 1.0 },
};

export function Orb({ state = "idle", size = 320, className, energy = 0 }) {
  const cfg = stateConfig[state] ?? stateConfig.idle;
  const e = Number.isFinite(energy) ? Math.max(0, Math.min(1, energy)) : 0;
  // Make the effect more visible: react to both speaking and user mic input.
  const boost = e * (state === "speaking" ? 0.09 : 0.05);

  return (
    <div
      className={cn("relative flex items-center justify-center", className)}
      style={{ width: size, height: size }}
    >
      <div
        className="absolute inset-0 rounded-full border"
        style={{ borderColor: `oklch(0 0 0 / ${0.04 + cfg.ringOpacity * 0.05})` }}
      />
      <div
        className="absolute rounded-full border"
        style={{
          inset: "8%",
          borderColor: `oklch(0 0 0 / ${0.03 + cfg.ringOpacity * 0.06})`,
        }}
      />

      <div
        className="absolute rounded-full"
        style={{
          width: "82%",
          height: "82%",
          top: "12%",
          background: "radial-gradient(circle, oklch(0 0 0 / 0.10), transparent 70%)",
          filter: "blur(28px)",
        }}
      />

      <motion.div
        className="relative rounded-full"
        style={{
          width: "70%",
          height: "70%",
          background:
            "radial-gradient(circle at 32% 26%, oklch(0.55 0 0) 0%, oklch(0.28 0 0) 55%, oklch(0.14 0 0) 100%)",
          boxShadow:
            "inset 0 -28px 60px oklch(0 0 0 / 0.55), inset 0 22px 40px oklch(1 0 0 / 0.10)",
        }}
        animate={{ scale: cfg.scale.map((v) => v + boost) }}
        transition={{ duration: cfg.duration, repeat: Infinity, ease: "easeInOut" }}
      >
        <div
          className="absolute rounded-full"
          style={{
            top: "10%",
            left: "20%",
            width: "38%",
            height: "26%",
            background: "radial-gradient(ellipse, oklch(1 0 0 / 0.22), transparent 70%)",
            filter: "blur(10px)",
          }}
        />
        <div
          className="absolute left-0 right-0 top-1/2 h-px"
          style={{ background: "oklch(1 0 0 / 0.06)" }}
        />
      </motion.div>
    </div>
  );
}

