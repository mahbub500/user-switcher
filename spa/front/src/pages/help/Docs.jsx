import { useState, useEffect } from 'react';
import parse from 'html-react-parser';
import axios from "axios";
import Header from '../../components/Header';
import Footer from '../../components/Footer';
import Loader from '../../components/Loader';
import { externalButtons } from '../../data';

const Docs = () => {

    const [ posts, setPosts ] = useState([]);
    const [ loading, setLoading ] = useState(true);

    useEffect( () => {
        axios.get('https://codexpert.io/wp-json/wp/v2/posts?per_page=10&_fields[]=id&_fields[]=link&_fields[]=excerpt&_fields[]=title').then((res) => {
            setPosts(res.data);
            setLoading(false);
        });
    }, [] );

    const postsHtml = [];

    { posts.map(post => {
        postsHtml.push(
            <div id={`user-switcher-help-`+post.id} className="user-switcher-help">
                <h2 className="user-switcher-help-heading" data-target={`#user-switcher-help-text-`+post.id}>
                    <a href={post.link} target="_blank">
                        <span className="dashicons dashicons-admin-links"></span>
                        <span className="heading-text">{parse(post.title.rendered)}</span>
                    </a>
                </h2>
                <div id={`user-switcher-help-text-`+post.id} className="user-switcher-help-text">
                    {parse(post.excerpt.rendered)}
                </div>
            </div>
        )
    })}

    const buttonsHtml = [];

    {externalButtons.map(button => {
        buttonsHtml.push(<a target="_blank" href={button.url} className="user-switcher-help-link">{button.label}</a>)
    })}

    return (
        <div className="wrap">
            <Header />

            <div className="user-switcher-help-tab cx-shadow">
                <div className="user-switcher-documentation">
                    <div id="user-switcher-helps">
                    { ! loading ? postsHtml : <Loader /> }
                    </div>
                </div>
                <div className="user-switcher-help-links">
                    {buttonsHtml}
                </div>
            </div>
            
            <Footer />
        </div>
    );
};

export default Docs;
