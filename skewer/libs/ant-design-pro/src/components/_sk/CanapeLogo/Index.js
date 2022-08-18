import React from 'react'
import logo from './files/logo-canape.svg'
import logoBall from './files/logo-ball.svg'
import logoText from './files/logo-text.svg'
import styles from './style.less'

export default ({ inverse }) => {
  return (
    <div className="logo-wrapper">
      {
        inverse ? (
          <>
            <img src={logoBall} alt="canape-ball" />
            <span className="sk-logo-text"><img src={logoText} alt="canape-text" /></span>
          </>
        ) : (
          <img src={logo} alt="canape-logo" />
        )
      }
    </div>
  );
}


