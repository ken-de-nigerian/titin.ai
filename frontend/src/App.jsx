import { useState } from 'react'
import './App.css'
import InterviewModal from './components/InterviewModal';

function App() {
  const [showInterview, setShowInterview] = useState(false);

  const handleStartClick = () => {
    setShowInterview(true)
  }

  return (
    <div className="appShell">
      <header className="topBar">
        <div className="brand">
          <span className="brandDot" />
          <span className="brandName">Lumen</span>
        </div>
      </header>

      <main className="landing">
        <div className="landingInner">
          <p className="kicker">AI interview practice</p>
          <h1 className="headline">
            Real-time mock interviews,
            <span className="headlineEm"> voice-first</span>.
          </h1>
          <p className="subhead">
            Start a live session with an AI interviewer. No sign-in yet — just jump in.
          </p>

          <button className="primaryCta" onClick={handleStartClick}>
            Start interview
          </button>
        </div>
      </main>

      {showInterview && <InterviewModal onClose={() => setShowInterview(false)} />}
    </div>
  )
}

export default App
