import useEmblaCarousel from "embla-carousel-react";
import { useCallback, useEffect, useState, type ReactNode } from "react";
import { ArrowLeft, ArrowRight } from "lucide-react";
import { cn } from "@/lib/utils";

interface SnapCarouselProps {
  children: ReactNode[];
  className?: string;
  showArrows?: boolean;
  slideClassName?: string;
}

export function SnapCarousel({
  children,
  className,
  showArrows = true,
  slideClassName,
}: SnapCarouselProps) {
  const [emblaRef, emblaApi] = useEmblaCarousel({ align: "start", dragFree: false, loop: false });
  const [selected, setSelected] = useState(0);
  const [count, setCount] = useState(0);

  useEffect(() => {
    if (!emblaApi) return;
    const onSelect = () => setSelected(emblaApi.selectedScrollSnap());
    setCount(emblaApi.scrollSnapList().length);
    onSelect();
    emblaApi.on("select", onSelect);
    emblaApi.on("reInit", () => {
      setCount(emblaApi.scrollSnapList().length);
      onSelect();
    });
  }, [emblaApi]);

  const scrollTo = useCallback((i: number) => emblaApi?.scrollTo(i), [emblaApi]);
  const prev = () => emblaApi?.scrollPrev();
  const next = () => emblaApi?.scrollNext();

  return (
    <div className={cn("relative", className)}>
      <div className="overflow-hidden" ref={emblaRef}>
        <div className="flex gap-4 -ml-1 pl-1">
          {children.map((child, i) => (
            <div key={i} className={cn("min-w-0 shrink-0 grow-0", slideClassName ?? "basis-[88%] md:basis-[44%] lg:basis-[33%]")}>
              {child}
            </div>
          ))}
        </div>
      </div>

      <div className="mt-8 flex items-center justify-between border-t border-hairline pt-5">
        <div className="flex items-center gap-3 text-xs text-muted-foreground tabular-nums">
          <span className="text-foreground">{String(selected + 1).padStart(2, "0")}</span>
          <span>/</span>
          <span>{String(count).padStart(2, "0")}</span>
        </div>
        <div className="flex gap-1">
          {Array.from({ length: count }).map((_, i) => (
            <button
              key={i}
              onClick={() => scrollTo(i)}
              aria-label={`Go to slide ${i + 1}`}
              className={cn(
                "h-px transition-all",
                i === selected ? "w-8 bg-foreground" : "w-4 bg-hairline hover:bg-muted-foreground"
              )}
              style={{ height: "2px" }}
            />
          ))}
        </div>
        {showArrows && (
          <div className="flex gap-1">
            <button
              onClick={prev}
              className="grid h-9 w-9 place-items-center rounded-full border border-hairline transition hover:bg-surface"
              aria-label="Previous"
            >
              <ArrowLeft className="h-4 w-4" />
            </button>
            <button
              onClick={next}
              className="grid h-9 w-9 place-items-center rounded-full border border-hairline transition hover:bg-surface"
              aria-label="Next"
            >
              <ArrowRight className="h-4 w-4" />
            </button>
          </div>
        )}
      </div>
    </div>
  );
}
