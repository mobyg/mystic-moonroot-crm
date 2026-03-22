"""
Image Generation Microservice using Emergent Universal Key
This service handles AI image generation for the Laravel app
"""

from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import Optional
import base64
import os
import asyncio
from dotenv import load_dotenv

load_dotenv()

from emergentintegrations.llm.openai.image_generation import OpenAIImageGeneration

app = FastAPI(title="Image Generation Service")

# Get the Emergent Universal Key
EMERGENT_LLM_KEY = os.environ.get('EMERGENT_LLM_KEY', 'sk-emergent-9D7B9B5Cd17C53e466')

class ImageRequest(BaseModel):
    prompt: str
    model: Optional[str] = "gpt-image-1"
    
class ImageResponse(BaseModel):
    success: bool
    image_base64: Optional[str] = None
    error: Optional[str] = None

@app.get("/health")
async def health():
    return {"status": "ok", "service": "image-generation"}

@app.post("/generate", response_model=ImageResponse)
async def generate_image(request: ImageRequest):
    """Generate an image using OpenAI via Emergent Universal Key"""
    try:
        image_gen = OpenAIImageGeneration(api_key=EMERGENT_LLM_KEY)
        
        images = await image_gen.generate_images(
            prompt=request.prompt,
            model=request.model,
            number_of_images=1
        )
        
        if images and len(images) > 0:
            image_base64 = base64.b64encode(images[0]).decode('utf-8')
            return ImageResponse(success=True, image_base64=image_base64)
        else:
            return ImageResponse(success=False, error="No image was generated")
            
    except Exception as e:
        return ImageResponse(success=False, error=str(e))

@app.post("/generate-product-images")
async def generate_product_images(
    genre: str,
    product_name: str,
    product_description: str
):
    """Generate all 3 product images (white_bg, black_tshirt, lifestyle)"""
    
    prompts = {
        'white_bg': f"A mystical {genre} t-shirt design: {product_name}. Beautiful spiritual artwork on pure white background, centered composition, high contrast, vector-style graphic suitable for t-shirt printing. {product_description}",
        
        'black_tshirt': f"A realistic black t-shirt mockup featuring a mystical {genre} design for {product_name}. Professional product photography, t-shirt laid flat on white background, design centered on chest area",
        
        'lifestyle': f"A happy person wearing a black t-shirt with mystical {genre} design. Lifestyle photography, natural lighting, spiritual/bohemian aesthetic, person smiling with positive energy, outdoor nature setting"
    }
    
    results = {}
    image_gen = OpenAIImageGeneration(api_key=EMERGENT_LLM_KEY)
    
    for image_type, prompt in prompts.items():
        try:
            images = await image_gen.generate_images(
                prompt=prompt,
                model="gpt-image-1",
                number_of_images=1
            )
            
            if images and len(images) > 0:
                results[image_type] = base64.b64encode(images[0]).decode('utf-8')
            else:
                results[image_type] = None
                
        except Exception as e:
            results[image_type] = None
            print(f"Error generating {image_type}: {e}")
    
    return {
        "success": True,
        "images": results
    }

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8002)
