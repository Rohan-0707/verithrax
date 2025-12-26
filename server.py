import os
import base64
import requests
from fastapi import FastAPI, Request
from fastapi.responses import JSONResponse
import uvicorn
import json
from openai import OpenAI
import re
from PIL import Image
from io import BytesIO
from rembg import remove, new_session
import replicate
from dotenv import load_dotenv

# Load environment variables from .env file (for local development)
# Railway will use environment variables directly
load_dotenv()

# --- STARK INDUSTRIES CONFIGURATION MK XIII (FLUX ENGINE) ---
# API Keys (required as environment variables)
OPENAI_API_KEY = os.getenv("OPENAI_API_KEY")
if not OPENAI_API_KEY:
    raise ValueError("OPENAI_API_KEY environment variable is required")

REPLICATE_API_TOKEN = os.getenv("REPLICATE_API_TOKEN")
if not REPLICATE_API_TOKEN:
    raise ValueError("REPLICATE_API_TOKEN environment variable is required")
os.environ["REPLICATE_API_TOKEN"] = REPLICATE_API_TOKEN

# WordPress credentials are now received dynamically from webhook payload
# This allows each WordPress site to authenticate with their own credentials
# ------------------------------------------------------------

app = FastAPI()
client = OpenAI(api_key=OPENAI_API_KEY)
rembg_session = new_session()

def get_wp_headers(wp_username, wp_app_password, is_image_upload=False):
    """
    Generate WordPress API headers using credentials from webhook payload.
    This allows each WordPress installation to use their own credentials.
    """
    credentials = f"{wp_username}:{wp_app_password}"
    token = base64.b64encode(credentials.encode())
    headers = {'Authorization': f'Basic {token.decode("utf-8")}'}
    if not is_image_upload:
        headers['Content-Type'] = 'application/json'
    return headers

def sanitize_filename(title):
    return re.sub(r'[^a-z0-9]', '-', title.lower())[:50] + ".png"

# --- CORE AI MODULES ---

def analyze_image_v3(image_url):
    """Quick visual analysis for background generation context."""
    print(f">> Engaging Visual Scanners: {image_url}...")
    try:
        response = client.chat.completions.create(
            model="gpt-4o",
            messages=[{
                "role": "user",
                "content": [
                    {"type": "text", "text": "Briefly describe this product's style, colors, and vibe for creating a matching background."},
                    {"type": "image_url", "image_url": {"url": image_url}},
                ],
            }],
            max_tokens=200,
        )
        return response.choices[0].message.content
    except Exception as e:
        return "modern streetwear aesthetic"

def generate_flux_masterpiece(original_image_url, product_name, visual_analysis):
    """
    Mark XIII: Uses Replicate's Flux Fill Pro to paint background while PROTECTING product pixels.
    """
    print(f">> Engaging FLUX.1 Fill Pro Engine for: {product_name}...")
    
    try:
        # 1. Download Original Image
        response = requests.get(original_image_url)
        original_img = Image.open(BytesIO(response.content)).convert("RGBA")
        
        # 2. Create the Mask (Black = Keep Product, White = Replace Background)
        print(">> Creating Protection Mask (Product Isolation)...")
        
        # Remove background to isolate product
        nobg_img = remove(original_img, session=rembg_session)
        
        # Create Mask: Extract Alpha channel
        # For Flux Fill: WHITE area gets regenerated (background), BLACK area stays (product)
        alpha = nobg_img.split()[3]
        mask = Image.eval(alpha, lambda a: 255 if a == 0 else 0)  # Invert: transparent→white, product→black
        
        # Resize for Flux (Must be multiples of 16, e.g., 1344x768 for landscape)
        target_w, target_h = 1344, 768 
        
        # Helper to center product on canvas
        def fit_on_canvas(img, mask_img, w, h):
            canvas = Image.new("RGBA", (w, h), (255, 255, 255, 255))  # White background
            mask_canvas = Image.new("L", (w, h), 255)  # White (fill everything by default)
            
            # Scale product to ~70% of canvas height
            ratio = min((w*0.8)/img.width, (h*0.7)/img.height)
            new_size = (int(img.width*ratio), int(img.height*ratio))
            
            img_resized = img.resize(new_size, Image.Resampling.LANCZOS)
            mask_resized = mask_img.resize(new_size, Image.Resampling.LANCZOS)
            
            # Center positioning
            x = (w - new_size[0]) // 2
            y = (h - new_size[1]) // 2
            
            # Paste product onto white canvas
            canvas.paste(img_resized, (x, y), img_resized)
            
            # Paste mask (black product shape onto white background)
            mask_canvas.paste(mask_resized, (x, y))
            
            return canvas, mask_canvas

        final_img, final_mask = fit_on_canvas(nobg_img, mask, target_w, target_h)
        
        # Save temp files for API upload
        final_img.save("temp_source.png")
        final_mask.save("temp_mask.png")

        # 3. Call Replicate (Flux Fill Pro)
        print(">> Transmitting to Replicate (Black Forest Labs)...")
        
        prompt = f"""Product photography of {product_name} in a premium luxury studio setting.
Dark moody atmosphere with subtle neon accent lighting.
Concrete or industrial textures in the background.
Soft professional rim lighting highlighting the product.
Clean, high-end aesthetic matching {visual_analysis}.
8k resolution, masterpiece quality, commercial photography."""
        
        output = replicate.run(
            "black-forest-labs/flux-fill-pro",
            input={
                "image": open("temp_source.png", "rb"),
                "mask": open("temp_mask.png", "rb"),
                "prompt": prompt,
                "guidance": 30,
                "output_format": "png",
                "safety_tolerance": 2,
                "num_outputs": 1
            }
        )
        
        # Cleanup temp files
        os.remove("temp_source.png")
        os.remove("temp_mask.png")

        print(f">> Flux Output Received")
        
        # 4. Download result
        # Flux Fill Pro returns a list with one URL
        if isinstance(output, list) and len(output) > 0:
            image_url = str(output[0])
        else:
            image_url = str(output)
            
        img_response = requests.get(image_url)
        gen_img = Image.open(BytesIO(img_response.content))
        
        # Final Resize to article header size (1248x612)
        gen_img = gen_img.resize((1248, 612), Image.Resampling.LANCZOS)
        
        output_io = BytesIO()
        gen_img.save(output_io, format='PNG')
        return output_io.getvalue()

    except Exception as e:
        print(f"FLUX Engine Error: {e}")
        import traceback
        traceback.print_exc()
        return None

def upload_raw_image(img_data, title, wp_base_url, wp_username, wp_app_password):
    if not img_data:
        return None
    print(">> Uploading Masterpiece to WordPress...")
    filename = sanitize_filename(title)
    url = f"{wp_base_url}/wp-json/wp/v2/media"
    headers = get_wp_headers(wp_username, wp_app_password, is_image_upload=True)
    headers['Content-Disposition'] = f'attachment; filename="{filename}"'
    headers['Content-Type'] = 'image/png'
    response = requests.post(url, headers=headers, data=img_data)
    if response.status_code == 201:
        media_id = response.json().get('id')
        print(f"Upload Success! Media ID: {media_id}")
        return media_id
    else:
        print(f"Upload Failed: {response.status_code} - {response.text}")
        return None

def write_html_masterpiece(product_name, description, visual_analysis, price_info, brand, founders, about_brand, product_link, original_image_url, attributes):
    """
    Mark X: 2-Column Card Layout with Mobile Responsiveness
    """
    print(f">> Designing 2-Column HTML Layout...")
    
    prompt = f"""You are a Senior Front-End Developer and Content Writer.

**CRITICAL TASK:** Create a premium product showcase article (2000-2500 words).

**OUTPUT FORMAT:** RAW HTML string wrapped in a SINGLE <div>. NO markdown. NO backticks. Just the HTML.

**DATA PROVIDED:**
- Product: {product_name}
- Brand: {brand}
- Founders: {founders}
- Product Link: {product_link}
- Description: {description}
- Visual Analysis: {visual_analysis}
- Price: {price_info}
- Attributes: {attributes}
- Brand Story: {about_brand}

**STRICT LAYOUT RULES (DESKTOP):**
1. **Top Section (2-Column Card):**
   - Left Column: Product Image Card
     * Width: ~350px
     * Image source: '{original_image_url}' (USE THIS EXACT URL)
     * Add box-shadow, border-radius, premium styling
     * Image should be responsive (max-width: 100%)
   - Right Column: Product Details
     * Product name (h3)
     * Price (large, bold text with premium styling)
     * Attributes list (Material, Color, GSM, etc.)
     * CTA Button: "View Product" linking to {product_link}
       - Button Style: Premium gradient, hover effects, rounded corners
       - Make it stand out visually

2. **Mobile Responsiveness:**
   - Use CSS @media queries
   - On screens < 768px: Stack image and details vertically
   - Image goes on top, details below

**IMAGE USAGE RULE:**
- USE '{original_image_url}' EXACTLY ONCE in the left card.
- DO NOT repeat the image anywhere else in the article.

**ARTICLE CONTENT (Below the card):**
- Write 2000+ words of engaging, SEO-optimized content
- Include brand story and mention founders ({founders})
- Use heading hierarchy: h3, h4, h5 ONLY (no h1, h2)
- Sections examples:
  * Why This Product Stands Out
  * Material & Craftsmanship
  * Style & Versatility
  * About {brand}
  * Final Verdict

**CSS STYLING:**
- Include ALL CSS in a <style> block at the top of the output
- Premium, modern design (gradients, shadows, clean typography)
- Professional color scheme
- Ensure text is readable and layout is clean

**FINAL OUTPUT:**
<div>
  <style>
    /* All CSS here */
  </style>
  
  <!-- 2-Column Card -->
  <div class="product-card">
    <div class="image-column">
      <img src="{original_image_url}" alt="{product_name}" />
    </div>
    <div class="details-column">
      <!-- Details and CTA button -->
    </div>
  </div>
  
  <!-- Article content (2000+ words) -->
  <h3>Why This Product Stands Out</h3>
  ...
</div>

BEGIN NOW. Output only the HTML."""

    try:
        response = client.chat.completions.create(
            model="gpt-4o",
            messages=[{"role": "user", "content": prompt}],
            max_tokens=4096,
            temperature=0.7
        )
        return response.choices[0].message.content
    except Exception as e:
        print(f"Content Generation Error: {e}")
        return f"<div><h3>{product_name}</h3><p>{description}</p></div>"

def post_final_draft(title, content, original_product_id, featured_image_id, wp_base_url, wp_username, wp_app_password):
    print(">> Assembling Final Draft...")
    url = f"{wp_base_url}/wp-json/wp/v2/posts"
    headers = get_wp_headers(wp_username, wp_app_password)
    post_data = {
        "title": f"Review: {title}",
        "content": content,
        "status": "draft",
        "categories": [1],
        "featured_media": featured_image_id,
        "meta": {"verithrax_source_product": original_product_id}
    }
    response = requests.post(url, headers=headers, json=post_data)
    if response.status_code == 201:
        post_id = response.json().get('id')
        print(f"SUCCESS! Draft ID: {post_id}")
        return True
    else:
        print(f"Post Creation Failed: {response.status_code} - {response.text}")
        return False

# --- THE WEBHOOK (ENTRY POINT) ---

@app.post("/webhook")
async def receive_webhook(request: Request):
    data = await request.json()
    
    # Extract product data
    p_id = data.get('post_id')
    title = data.get('post_title')
    desc = data.get('post_content')
    img = data.get('product_image')
    link = data.get('product_link', '#')
    
    # Pricing
    regular_price = data.get('regular_price')
    sale_price = data.get('sale_price')
    price_display = data.get('price_display', 'Check Price')
    
    # Attributes
    attributes = data.get('attributes', '')
    
    # Brand info from WordPress settings
    brand = data.get('brand_name', 'Our Brand')
    founders = data.get('founders_name', 'The Team')
    about_brand = data.get('about_brand', '')
    
    # WordPress credentials (sent from plugin settings)
    wp_base_url = data.get('wp_base_url', '')
    wp_username = data.get('wp_username', '')
    wp_app_password = data.get('wp_app_password', '')
    
    # Validate WordPress credentials
    if not wp_base_url or not wp_username or not wp_app_password:
        print("ERROR: Missing WordPress credentials in webhook payload.")
        print("Please configure WordPress API credentials in the plugin settings.")
        return JSONResponse(
            status_code=400, 
            content={"status": "error", "message": "Missing WordPress credentials"}
        )
    
    price_info = f"Price: {price_display}"
    if regular_price and sale_price and regular_price != sale_price:
        price_info = f"Sale Price: {sale_price} (Regular: {regular_price})"
    
    print("\n" + "="*60)
    print(f"INITIATING MARK XIII (FLUX ENGINE) FOR: {brand} ({title})")
    print(f"Target WordPress: {wp_base_url}")
    
    if img and title:
        # Phase 1: Visual Analysis (for background context)
        visuals = analyze_image_v3(img)
        
        # Phase 2: FLUX FILL PRO (100% Product Fidelity + New Background)
        thumbnail_bytes = generate_flux_masterpiece(img, title, visuals)
        
        # Phase 3: Upload Thumbnail (using dynamic credentials)
        media_id = upload_raw_image(thumbnail_bytes, title, wp_base_url, wp_username, wp_app_password)
        
        # Phase 4: Generate Premium HTML Article
        article_html = write_html_masterpiece(
            title, desc, visuals, price_info, brand, founders, about_brand, 
            link, img, attributes
        )
        
        # Phase 5: Publish as Draft (using dynamic credentials)
        post_final_draft(title, article_html, p_id, media_id, wp_base_url, wp_username, wp_app_password)
        
    else:
        print("Error: Missing required data (image or title).")
        
    print("="*60 + "\n")
    return JSONResponse(status_code=200, content={"status": "processing_mk13"})

if __name__ == "__main__":
    print("VERITHRAX MARK XIII ONLINE. FLUX ENGINE ENGAGED.")
    port = int(os.getenv("PORT", 8000))  # Railway sets PORT environment variable
    uvicorn.run(app, host="0.0.0.0", port=port)
