import React from 'react';
import classNames from 'classnames';
import styles from './style.less';

export default ({ items, selectedItemId, handleClick }) => {
  return (
    <ul
      className="b-sk-list"
    >
      {
        items.map(item => {
          return (
            <li
              key={item.id}
              className={classNames('sk-list-item', {
                'sk-list-item--selected': selectedItemId === item.id,
              })}
              onClick={handleClick(item.id, selectedItemId)}
            >
              {item.title}
            </li>
          );
        })
      }
    </ul>
  );
}
