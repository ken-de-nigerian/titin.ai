import { createRootRoute, createRoute, createRouter } from "@tanstack/react-router";
import LandingPage from "@/pages/LandingPage";
import DashboardPage from "@/pages/DashboardPage";
import FeedbackPage from "@/pages/FeedbackPage";
import AuthPage from "@/pages/AuthPage";
import InterviewRoute from "@/pages/InterviewRoute";

const rootRoute = createRootRoute({
  component: () => <div className="min-h-screen"><Outlet /></div>,
});

import { Outlet } from "@tanstack/react-router";

const indexRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: "/",
  component: LandingPage,
});

const dashboardRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: "/dashboard",
  component: DashboardPage,
});

const interviewRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: "/interview",
  component: InterviewRoute,
});

const feedbackRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: "/feedback",
  component: FeedbackPage,
});

const authRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: "/auth",
  component: AuthPage,
});

const routeTree = rootRoute.addChildren([
  indexRoute,
  dashboardRoute,
  interviewRoute,
  feedbackRoute,
  authRoute,
]);

export const router = createRouter({ routeTree });

